(function () {
  function ready(fn) {
    if (document.readyState !== 'loading') fn();
    else document.addEventListener('DOMContentLoaded', fn);
  }

  function showMapError(canvas, message) {
    if (!canvas) return;
    canvas.classList.remove('is-loading');
    canvas.innerHTML =
      '<div style="display:grid;place-items:center;height:100%;color:#64748b;font-weight:600;padding:1.25rem;text-align:center;">' +
      (message || 'Unable to load map.') +
      '</div>';
  }

  ready(function () {
    var canvas = document.getElementById('rbim-siquijor-map');
    if (!canvas) return;

    if (typeof L === 'undefined') {
      showMapError(canvas, 'Map library failed to load.');
      return;
    }

    var geoUrl = canvas.getAttribute('data-geojson') || '';
    var brgyGeoUrl = canvas.getAttribute('data-barangay-geojson') || '';
    var focusMunicipalityName = canvas.getAttribute('data-focus-municipality') || '';
    var isMuniFocus = focusMunicipalityName !== '' || !!canvas.closest('.rbim-map-panel--muni');
    if (!focusMunicipalityName && isMuniFocus) {
      focusMunicipalityName = canvas.getAttribute('data-municipality') || '';
    }
    var stats = {};
    var barangayLists = {};
    try {
      stats = JSON.parse(canvas.getAttribute('data-stats') || '{}') || {};
    } catch (e) {
      stats = {};
    }
    try {
      barangayLists = JSON.parse(canvas.getAttribute('data-barangays') || '{}') || {};
    } catch (e) {
      barangayLists = {};
    }

    canvas.style.width = '';
    canvas.style.height = '';

    var defaultBounds = L.latLngBounds([9.08, 123.43], [9.32, 123.72]);
    var provinceBounds = defaultBounds;
    var fitPad = isMuniFocus ? [48, 48] : [0, 0];
    var focusMaskLayer = null;
    var focusBorderLayer = null;
    var focusMunicipalityFeature = null;
    var focusMuniBounds = null;

    var map;
    try {
      map = L.map(canvas, {
        zoomControl: false,
        scrollWheelZoom: true,
        attributionControl: true,
        maxBounds: isMuniFocus ? undefined : defaultBounds.pad(0.35),
        maxBoundsViscosity: 1,
        minZoom: isMuniFocus ? 11 : 10,
        maxZoom: 15
      });
    } catch (err) {
      showMapError(canvas, 'Unable to create map view.');
      return;
    }

    L.control.zoom({ position: 'topright' }).addTo(map);
    canvas.style.background = '#aad3df';

    if (isMuniFocus) {
      map.createPane('rbimMuniBorderPane');
      map.getPane('rbimMuniBorderPane').style.zIndex = 520;
      map.on('resize', fitFocusMunicipality);
    }

    var usingFallback = false;
    var osmFallback = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 15,
      minZoom: 10,
      noWrap: true,
      keepBuffer: 3,
      updateWhenIdle: true,
      updateWhenZooming: false,
      crossOrigin: true,
      attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
    });

    var cyclosm = L.tileLayer('https://{s}.tile-cyclosm.openstreetmap.fr/cyclosm/{z}/{x}/{y}.png', {
      maxZoom: 15,
      minZoom: 10,
      noWrap: true,
      keepBuffer: 3,
      updateWhenIdle: true,
      updateWhenZooming: false,
      crossOrigin: true,
      attribution:
        '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> · <a href="https://www.cyclosm.org">CyclOSM</a>'
    });

    cyclosm.on('tileerror', function () {
      if (usingFallback) return;
      usingFallback = true;
      try {
        map.removeLayer(cyclosm);
      } catch (e) {}
      if (!map.hasLayer(osmFallback)) osmFallback.addTo(map);
    });
    cyclosm.addTo(map);

    if (hintEl && isMuniFocus) {
      hintEl.textContent = 'CyclOSM · click a barangay';
    }

    var layerByName = {};
    var labelByName = {};
    var barangayGeo = null;
    var barangayLayerGroup = null;
    var brgyLayerByKey = {};
    var brgyLoadPromise = null;
    var activeBarangay = null;
    var legendEl = document.getElementById('rbim-map-legend');
    var legendSideTitleEl = document.getElementById('rbim-map-side-title');
    var selectedNameEl = document.getElementById('rbim-map-selected-name');
    var selectedSubEl = document.getElementById('rbim-map-selected-sub');
    var selectedGridEl = document.getElementById('rbim-map-selected-grid');
    var resetBtn = document.getElementById('rbim-map-reset');
    var floatEl = document.getElementById('rbim-map-float');
    var floatTitleEl = document.getElementById('rbim-map-float-title');
    var floatMetricsEl = document.getElementById('rbim-map-float-metrics');
    var floatListEl = document.getElementById('rbim-map-float-list');
    var floatCloseBtn = document.getElementById('rbim-map-float-close');
    var floatDragEl = document.getElementById('rbim-map-float-drag');
    var floatResizeEl = document.getElementById('rbim-map-float-resize');
    var hintEl = document.getElementById('rbim-map-hint');
    var stageEl = canvas.parentElement;
    var activeName = null;
    var floatMoved = false;

    var FLOAT_MIN_W = 260;
    var FLOAT_MIN_H = 240;
    var FLOAT_PAD = 8;
    var BRGY_COLORS = ['#dbeafe', '#dcfce7', '#fef3c7', '#fce7f3', '#e0e7ff', '#ffedd5', '#e0f2fe', '#f3e8ff'];

    function brgyKey(municipality, barangay) {
      return municipality + '|' + barangay;
    }

    function brgyStyle(isActive, colorIndex) {
      return {
        stroke: true,
        color: isActive ? '#0f172a' : '#334155',
        weight: isActive ? 2.6 : 1.15,
        opacity: isActive ? 1 : 0.8,
        fillColor: isActive ? '#fbbf24' : BRGY_COLORS[colorIndex % BRGY_COLORS.length],
        fillOpacity: isActive ? 0.28 : 0.16,
        lineJoin: 'round',
        lineCap: 'round'
      };
    }

    function clearBarangayTiles() {
      activeBarangay = null;
      brgyLayerByKey = {};
      if (barangayLayerGroup) {
        try {
          map.removeLayer(barangayLayerGroup);
        } catch (e) {}
        barangayLayerGroup = null;
      }
      if (floatListEl) {
        floatListEl.querySelectorAll('.is-active').forEach(function (el) {
          el.classList.remove('is-active');
        });
      }
    }

    function setActiveBarangay(barangayName) {
      activeBarangay = barangayName || null;
      Object.keys(brgyLayerByKey).forEach(function (key) {
        var layer = brgyLayerByKey[key];
        var name = key.split('|')[1];
        var on = name === activeBarangay;
        layer.setStyle(brgyStyle(on, layer._rbimColorIdx || 0));
        if (on && layer.bringToFront) layer.bringToFront();
      });
      if (floatListEl) {
        floatListEl.querySelectorAll('li[data-barangay]').forEach(function (el) {
          el.classList.toggle('is-active', el.getAttribute('data-barangay') === activeBarangay);
        });
        var activeLi = floatListEl.querySelector('li.is-active');
        if (activeLi && activeLi.scrollIntoView) {
          activeLi.scrollIntoView({ block: 'nearest' });
        }
      }
      if (legendEl && isMuniFocus) {
        legendEl.querySelectorAll('.rbim-map-legend-item').forEach(function (el) {
          el.classList.toggle('is-active', el.getAttribute('data-barangay') === activeBarangay);
        });
      }
      if (isMuniFocus && focusMunicipalityName && layerByName[focusMunicipalityName] && layerByName[focusMunicipalityName].bringToFront) {
        layerByName[focusMunicipalityName].bringToFront();
      }
      raiseMunicipalityBorder();
    }

    function showBarangayTiles(municipality) {
      clearBarangayTiles();
      if (!barangayGeo || !barangayGeo.features || !municipality) return;

      var features = barangayGeo.features.filter(function (f) {
        var muni = f.properties && f.properties.municipality;
        return muni && municipality && muni.toLowerCase() === municipality.toLowerCase();
      });
      if (!features.length) return;

      barangayLayerGroup = L.geoJSON(
        { type: 'FeatureCollection', features: features },
        {
          style: function (feature) {
            var idx = 0;
            var fname = feature.properties && feature.properties.name;
            for (var i = 0; i < features.length; i++) {
              if (features[i].properties && features[i].properties.name === fname) {
                idx = i;
                break;
              }
            }
            return brgyStyle(false, idx);
          },
          onEachFeature: function (feature, featureLayer) {
            var brgyName = feature.properties.name;
            var key = brgyKey(municipality, brgyName);
            var colorIdx = Object.keys(brgyLayerByKey).length;
            brgyLayerByKey[key] = featureLayer;
            featureLayer._rbimColorIdx = colorIdx;
            featureLayer.bindTooltip(brgyName, {
              sticky: true,
              direction: 'top',
              opacity: 0.95,
              className: 'rbim-map-brgy-tooltip'
            });
            featureLayer.on('click', function (e) {
              if (e && e.originalEvent) L.DomEvent.stopPropagation(e.originalEvent);
              setActiveBarangay(brgyName);
            });
          }
        }
      ).addTo(map);

      // Keep municipality outline above barangay fills.
      if (layerByName[municipality] && layerByName[municipality].bringToFront) {
        layerByName[municipality].bringToFront();
      }
      if (isMuniFocus) {
        raiseMunicipalityBorder();
      }
      Object.keys(labelByName).forEach(function (key) {
        if (labelByName[key] && labelByName[key].bringToFront) {
          labelByName[key].bringToFront();
        }
      });
    }

    function ensureBarangayGeo() {
      if (barangayGeo) {
        return Promise.resolve(barangayGeo);
      }
      if (brgyLoadPromise) return brgyLoadPromise;

      var embeddedBrgy = window.RBIM_SIQUIJOR_BARANGAY_GEOJSON;
      if (embeddedBrgy && embeddedBrgy.features && embeddedBrgy.features.length) {
        barangayGeo = embeddedBrgy;
        return Promise.resolve(barangayGeo);
      }

      if (!brgyGeoUrl) {
        return Promise.reject(new Error('No barangay geojson'));
      }
      brgyLoadPromise = fetch(brgyGeoUrl, { credentials: 'same-origin' })
        .then(function (res) {
          if (!res.ok) throw new Error('HTTP ' + res.status);
          return res.json();
        })
        .then(function (geo) {
          barangayGeo = geo;
          return geo;
        })
        .catch(function (err) {
          brgyLoadPromise = null;
          throw err;
        });
      return brgyLoadPromise;
    }

    function fmt(n) {
      return Number(n || 0).toLocaleString();
    }

    function shortName(name) {
      return name === 'Enrique Villanueva' ? 'E. Villanueva' : name;
    }

    function styleFor(name, isActive) {
      if (isMuniFocus) {
        return {
          stroke: !!isActive,
          color: '#111827',
          weight: isActive ? 3 : 0,
          opacity: isActive ? 1 : 0,
          fillColor: '#ffffff',
          fillOpacity: 0,
          lineJoin: 'round',
          lineCap: 'round'
        };
      }

      return {
        stroke: !!isActive,
        color: '#111827',
        weight: isActive ? 3 : 0,
        opacity: isActive ? 1 : 0,
        fillColor: '#ffffff',
        fillOpacity: 0,
        lineJoin: 'round',
        lineCap: 'round'
      };
    }

    function updateSelectedCard(name) {
      if (!selectedNameEl || !selectedSubEl || !selectedGridEl) return;
      if (!name) {
        if (isMuniFocus) {
          var focusStats = stats[focusMunicipalityName] || {};
          selectedNameEl.textContent = focusMunicipalityName;
          selectedSubEl.textContent = 'Municipality coverage summary';
          selectedGridEl.innerHTML =
            '<div><span>Barangays</span><strong>' + fmt(focusStats.barangays) + '</strong></div>' +
            '<div><span>Households</span><strong>' + fmt(focusStats.households) + '</strong></div>' +
            '<div><span>Residents</span><strong>' + fmt(focusStats.residents) + '</strong></div>';
          return;
        }

        selectedNameEl.textContent = 'Province overview';
        selectedSubEl.textContent = 'CyclOSM view · click a municipality';
        var totals = Object.keys(stats).reduce(function (acc, key) {
          acc.barangays += Number((stats[key] && stats[key].barangays) || 0);
          acc.households += Number((stats[key] && stats[key].households) || 0);
          acc.residents += Number((stats[key] && stats[key].residents) || 0);
          return acc;
        }, { barangays: 0, households: 0, residents: 0 });
        selectedGridEl.innerHTML =
          '<div><span>Barangays</span><strong>' + fmt(totals.barangays) + '</strong></div>' +
          '<div><span>Households</span><strong>' + fmt(totals.households) + '</strong></div>' +
          '<div><span>Residents</span><strong>' + fmt(totals.residents) + '</strong></div>';
        return;
      }

      var s = stats[name] || {};
      selectedNameEl.textContent = name;
      selectedSubEl.textContent = 'Municipality coverage summary';
      selectedGridEl.innerHTML =
        '<div><span>Barangays</span><strong>' + fmt(s.barangays) + '</strong></div>' +
        '<div><span>Households</span><strong>' + fmt(s.households) + '</strong></div>' +
        '<div><span>Residents</span><strong>' + fmt(s.residents) + '</strong></div>';
    }

    function setActive(name) {
      activeName = name || null;
      Object.keys(layerByName).forEach(function (key) {
        var layer = layerByName[key];
        var on = key === name;
        layer.setStyle(styleFor(key, on));
        if (on) layer.bringToFront();
      });
      Object.keys(labelByName).forEach(function (key) {
        if (labelByName[key] && labelByName[key].bringToFront) {
          labelByName[key].bringToFront();
        }
      });
      if (legendEl) {
        legendEl.querySelectorAll('.rbim-map-legend-item').forEach(function (btn) {
          btn.classList.toggle('is-active', btn.getAttribute('data-name') === name);
        });
      }
      updateSelectedCard(name);
      if (isMuniFocus) raiseMunicipalityBorder();
    }

    function clamp(n, min, max) {
      return Math.max(min, Math.min(max, n));
    }

    function stageSize() {
      return {
        w: stageEl ? stageEl.clientWidth : 800,
        h: stageEl ? stageEl.clientHeight : 520
      };
    }

    function placeFloat(left, top, width, height) {
      if (!floatEl) return;
      var stage = stageSize();
      var w = clamp(width != null ? width : floatEl.offsetWidth, FLOAT_MIN_W, stage.w - FLOAT_PAD * 2);
      var h = clamp(height != null ? height : floatEl.offsetHeight, FLOAT_MIN_H, stage.h - FLOAT_PAD * 2);
      var x = clamp(left, FLOAT_PAD, Math.max(FLOAT_PAD, stage.w - w - FLOAT_PAD));
      var y = clamp(top, FLOAT_PAD, Math.max(FLOAT_PAD, stage.h - h - FLOAT_PAD));
      floatEl.style.width = w + 'px';
      floatEl.style.height = h + 'px';
      floatEl.style.left = x + 'px';
      floatEl.style.top = y + 'px';
      floatEl.style.right = 'auto';
      floatEl.style.bottom = 'auto';
    }

    function resetFloatPosition() {
      if (!floatEl) return;
      floatMoved = false;
      placeFloat(14, 14, 320, 420);
    }

    function bindFloatInteractions() {
      if (!floatEl || !floatDragEl || !floatResizeEl) return;

      var dragState = null;
      var resizeState = null;

      function onDragMove(e) {
        if (!dragState) return;
        placeFloat(
          dragState.left + (e.clientX - dragState.startX),
          dragState.top + (e.clientY - dragState.startY),
          dragState.width,
          dragState.height
        );
        floatMoved = true;
      }

      function onDragUp() {
        dragState = null;
        floatEl.classList.remove('is-dragging');
        document.removeEventListener('pointermove', onDragMove);
        document.removeEventListener('pointerup', onDragUp);
      }

      function onResizeMove(e) {
        if (!resizeState) return;
        placeFloat(
          resizeState.left,
          resizeState.top,
          resizeState.width + (e.clientX - resizeState.startX),
          resizeState.height + (e.clientY - resizeState.startY)
        );
        floatMoved = true;
      }

      function onResizeUp() {
        resizeState = null;
        floatEl.classList.remove('is-resizing');
        document.removeEventListener('pointermove', onResizeMove);
        document.removeEventListener('pointerup', onResizeUp);
      }

      floatDragEl.addEventListener('pointerdown', function (e) {
        if (e.button != null && e.button !== 0) return;
        if (e.target && e.target.closest && e.target.closest('.rbim-map-float-close')) return;
        e.preventDefault();
        dragState = {
          startX: e.clientX,
          startY: e.clientY,
          left: floatEl.offsetLeft,
          top: floatEl.offsetTop,
          width: floatEl.offsetWidth,
          height: floatEl.offsetHeight
        };
        floatEl.classList.add('is-dragging');
        document.addEventListener('pointermove', onDragMove);
        document.addEventListener('pointerup', onDragUp);
      });

      floatResizeEl.addEventListener('pointerdown', function (e) {
        if (e.button != null && e.button !== 0) return;
        e.preventDefault();
        e.stopPropagation();
        resizeState = {
          startX: e.clientX,
          startY: e.clientY,
          left: floatEl.offsetLeft,
          top: floatEl.offsetTop,
          width: floatEl.offsetWidth,
          height: floatEl.offsetHeight
        };
        floatEl.classList.add('is-resizing');
        document.addEventListener('pointermove', onResizeMove);
        document.addEventListener('pointerup', onResizeUp);
      });
    }

    bindFloatInteractions();

    function autoPlaceFloat(layer, itemCount) {
      if (!floatEl) return;

      var stage = stageSize();
      var w = clamp(310, FLOAT_MIN_W, Math.max(FLOAT_MIN_W, stage.w - FLOAT_PAD * 2));
      var rowH = 46;
      var chromeH = 168;
      var h = clamp(
        chromeH + Math.max(itemCount, 1) * rowH + 16,
        FLOAT_MIN_H,
        Math.max(FLOAT_MIN_H, stage.h - FLOAT_PAD * 2)
      );

      var left = FLOAT_PAD + 6;
      var top = FLOAT_PAD + 6;

      if (isMuniFocus) {
        left = stage.w - w - FLOAT_PAD - 8;
        top = FLOAT_PAD + 6;
        placeFloat(left, top, w, h);
        floatMoved = false;
        return;
      }

      if (layer) {
        try {
          var b = layer.getBounds();
          var sw = map.latLngToContainerPoint(b.getSouthWest());
          var ne = map.latLngToContainerPoint(b.getNorthEast());
          var munLeft = Math.min(sw.x, ne.x);
          var munRight = Math.max(sw.x, ne.x);
          var munTop = Math.min(sw.y, ne.y);
          var munBottom = Math.max(sw.y, ne.y);
          var munCenterX = (munLeft + munRight) / 2;

          var spaceLeft = munLeft - FLOAT_PAD;
          var spaceRight = stage.w - munRight - FLOAT_PAD;

          if (spaceRight >= w + 16 && spaceRight >= spaceLeft) {
            left = munRight + 14;
          } else if (spaceLeft >= w + 16) {
            left = munLeft - w - 14;
          } else if (munCenterX > stage.w / 2) {
            left = FLOAT_PAD + 8;
          } else {
            left = stage.w - w - FLOAT_PAD - 8;
          }

          // Prefer aligning near the municipality top; fall back if it overflows.
          top = munTop;
          if (top + h > stage.h - FLOAT_PAD) {
            top = stage.h - h - FLOAT_PAD;
          }
          if (munBottom < stage.h * 0.45) {
            top = Math.max(FLOAT_PAD + 6, munBottom + 12);
          }
        } catch (e) {}
      }

      placeFloat(left, top, w, h);
      floatMoved = false;
    }

    function openFloat(name, layer) {
      if (!floatEl || !floatTitleEl || !floatMetricsEl || !floatListEl) return;

      var s = stats[name] || {};
      var list = barangayLists[name] || [];

      floatTitleEl.textContent = name;
      floatMetricsEl.innerHTML =
        '<div><span>Barangays</span><strong>' + fmt(list.length || s.barangays) + '</strong></div>' +
        '<div><span>Households</span><strong>' + fmt(s.households) + '</strong></div>' +
        '<div><span>Residents</span><strong>' + fmt(s.residents) + '</strong></div>';

      floatListEl.innerHTML = '';
      if (!list.length) {
        var empty = document.createElement('li');
        empty.className = 'rbim-map-float-empty';
        empty.textContent = 'No barangay records found.';
        floatListEl.appendChild(empty);
      } else {
        list.forEach(function (item) {
          var li = document.createElement('li');
          li.setAttribute('data-barangay', item.name || '');
          li.innerHTML =
            '<span class="rbim-map-float-brgy"></span>' +
            '<span class="rbim-map-float-counts">' +
            '<em>HH <strong></strong></em>' +
            '<em>Res <strong></strong></em>' +
            '</span>';
          li.querySelector('.rbim-map-float-brgy').textContent = item.name || '—';
          var strongs = li.querySelectorAll('strong');
          strongs[0].textContent = fmt(item.households);
          strongs[1].textContent = fmt(item.residents);
          li.addEventListener('click', function () {
            setActiveBarangay(item.name);
            var key = brgyKey(name, item.name);
            var layer = brgyLayerByKey[key];
            if (layer) {
              try {
                map.fitBounds(layer.getBounds(), { padding: [40, 40], maxZoom: 15 });
              } catch (e) {}
            }
          });
          floatListEl.appendChild(li);
        });
      }

      floatEl.hidden = false;
      floatEl.classList.add('is-open');
      if (hintEl) hintEl.hidden = true;

      // Initial place, then refine after the map finishes zooming to the municipality.
      autoPlaceFloat(layer, list.length);
      return list.length;
    }

    function fitFocusMunicipality() {
      if (!isMuniFocus) return;

      var bounds = null;
      if (focusMuniBounds && focusMuniBounds.isValid && focusMuniBounds.isValid()) {
        bounds = focusMuniBounds;
      }
      if ((!bounds || !bounds.isValid()) && focusMunicipalityName && layerByName[focusMunicipalityName]) {
        try {
          bounds = layerByName[focusMunicipalityName].getBounds();
        } catch (e) {}
      }
      if ((!bounds || !bounds.isValid()) && focusMunicipalityFeature) {
        try {
          bounds = L.geoJSON(focusMunicipalityFeature).getBounds();
        } catch (e) {}
      }
      if (!bounds || !bounds.isValid()) return;

      focusMuniBounds = bounds;
      try {
        map.fitBounds(bounds, { padding: [40, 40], maxZoom: 15, animate: false });
      } catch (e) {}
    }

    function scheduleMuniZoom() {
      if (!isMuniFocus) return;
      fitFocusMunicipality();
      setTimeout(fitFocusMunicipality, 120);
      setTimeout(fitFocusMunicipality, 400);
    }

    function closeFloat(resetView) {
      if (floatEl) {
        floatEl.classList.remove('is-open');
        if (!isMuniFocus) {
          floatEl.hidden = true;
        }
      }
      if (hintEl) hintEl.hidden = isMuniFocus;
      floatMoved = false;
      if (isMuniFocus) {
        setActiveBarangay(null);
        if (floatEl) {
          floatEl.hidden = false;
          floatEl.classList.add('is-open');
        }
        if (resetView !== false) {
          fitFocusMunicipality();
        }
        return;
      }

      clearBarangayTiles();
      setActive(null);
      if (resetView !== false) {
        try {
          map.fitBounds(provinceBounds, { padding: fitPad, maxZoom: 15 });
        } catch (e) {}
      }
    }

    function focusMunicipality(name) {
      var layer = layerByName[name];
      if (!layer) return;
      setActive(name);

      var listCount = openFloat(name, layer) || 0;

      var placeAfterZoom = function () {
        autoPlaceFloat(layer, listCount);
      };

      ensureBarangayGeo()
        .then(function () {
          showBarangayTiles(name);
        })
        .catch(function () {
          // Municipality zoom still works without barangay tiles.
        });

      try {
        map.once('moveend', placeAfterZoom);
        map.fitBounds(layer.getBounds(), {
          padding: [48, 48],
          maxZoom: 14
        });
        setTimeout(placeAfterZoom, 280);
      } catch (e) {
        placeAfterZoom();
      }
    }

    function buildBarangayLegend(municipality) {
      if (!legendEl) return;
      legendEl.innerHTML = '';
      var list = barangayLists[municipality] || [];
      list.forEach(function (item) {
        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'rbim-map-legend-item';
        btn.setAttribute('data-barangay', item.name || '');
        btn.innerHTML =
          '<span class="rbim-map-legend-text"><strong></strong><span></span></span>' +
          '<span class="rbim-map-legend-arrow" aria-hidden="true">›</span>';
        btn.querySelector('strong').textContent = item.name || '—';
        btn.querySelector('.rbim-map-legend-text > span').textContent =
          fmt(item.households) + ' HH · ' + fmt(item.residents) + ' residents';
        btn.addEventListener('click', function () {
          setActiveBarangay(item.name);
          legendEl.querySelectorAll('.rbim-map-legend-item').forEach(function (el) {
            el.classList.toggle('is-active', el === btn);
          });
          var key = brgyKey(municipality, item.name);
          var layer = brgyLayerByKey[key];
          if (layer) {
            try {
              map.fitBounds(layer.getBounds(), { padding: [40, 40], maxZoom: 15 });
            } catch (e) {}
          }
        });
        legendEl.appendChild(btn);
      });
    }

    function buildLegend(names) {
      if (!legendEl || isMuniFocus) return;
      legendEl.innerHTML = '';
      names.forEach(function (name) {
        var s = stats[name] || {};
        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'rbim-map-legend-item';
        btn.setAttribute('data-name', name);
        btn.innerHTML =
          '<span class="rbim-map-legend-text"><strong></strong><span></span></span>' +
          '<span class="rbim-map-legend-arrow" aria-hidden="true">›</span>';
        btn.querySelector('strong').textContent = name;
        btn.querySelector('.rbim-map-legend-text > span').textContent =
          fmt(s.barangays) + ' brgy · ' + fmt(s.households) + ' HH';
        btn.addEventListener('click', function () {
          focusMunicipality(name);
        });
        legendEl.appendChild(btn);
      });
    }

    function ringFromFeature(feature) {
      if (!feature || !feature.geometry) return null;
      var geom = feature.geometry;
      var ring;

      if (geom.type === 'Polygon') {
        ring = geom.coordinates[0];
      } else if (geom.type === 'MultiPolygon') {
        ring = geom.coordinates[0][0];
      } else {
        return null;
      }

      return ring.map(function (coord) {
        return [coord[1], coord[0]];
      });
    }

    function addFocusMask(feature) {
      if (!isMuniFocus || !feature) return;
      var hole = ringFromFeature(feature);
      if (!hole || !hole.length) return;

      if (focusMaskLayer) {
        try {
          map.removeLayer(focusMaskLayer);
        } catch (e) {}
        focusMaskLayer = null;
      }

      var outer = [
        [-85, -175],
        [-85, 175],
        [85, 175],
        [85, -175]
      ];

      focusMaskLayer = L.polygon([outer, hole], {
        fillColor: '#f4f6fa',
        fillOpacity: 0.94,
        stroke: false,
        interactive: false
      }).addTo(map);
    }

    function syncMunicipalityBorder(feature) {
      if (!isMuniFocus || !feature) return;

      focusMunicipalityFeature = feature;

      if (focusBorderLayer) {
        try {
          map.removeLayer(focusBorderLayer);
        } catch (e) {}
        focusBorderLayer = null;
      }

      var halo = L.geoJSON(feature, {
        pane: 'rbimMuniBorderPane',
        interactive: false,
        style: function () {
          return {
            stroke: true,
            color: '#ffffff',
            weight: 6,
            opacity: 0.95,
            fill: false,
            fillOpacity: 0,
            lineJoin: 'round',
            lineCap: 'round'
          };
        }
      });

      var line = L.geoJSON(feature, {
        pane: 'rbimMuniBorderPane',
        interactive: false,
        style: function () {
          return {
            stroke: true,
            color: '#111827',
            weight: 3,
            opacity: 1,
            fill: false,
            fillOpacity: 0,
            lineJoin: 'round',
            lineCap: 'round'
          };
        }
      });

      focusBorderLayer = L.layerGroup([halo, line]).addTo(map);
      raiseMunicipalityBorder();
    }

    function raiseMunicipalityBorder() {
      if (!focusBorderLayer) return;
      focusBorderLayer.eachLayer(function (layer) {
        if (layer.bringToFront) layer.bringToFront();
      });
      if (focusBorderLayer.bringToFront) focusBorderLayer.bringToFront();
    }

    function initMuniDashboardView(muniBounds) {
      try {
        focusMuniBounds = muniBounds;
        provinceBounds = muniBounds;
        map.setMaxBounds(muniBounds.pad(0.1));
        map.fitBounds(muniBounds, { padding: [40, 40], maxZoom: 15, animate: false });
        var minZ = map.getBoundsZoom(muniBounds, false, [40, 40]);
        if (!isFinite(minZ) || minZ < 1) minZ = 12;
        map.setMinZoom(Math.max(11, Math.min(15, minZ)));
      } catch (e) {
        map.setView([9.2, 123.55], 13);
      }
    }

    function lockMunicipality(muniBounds) {
      try {
        provinceBounds = muniBounds;
        map.setMaxBounds(muniBounds.pad(0.05));
        var minZ = map.getBoundsZoom(muniBounds, false, fitPad);
        if (!isFinite(minZ) || minZ < 1) minZ = 12;
        map.setMinZoom(Math.max(11, Math.min(14, minZ)));
        map.fitBounds(muniBounds, { padding: fitPad, maxZoom: 14 });
      } catch (e) {
        map.setView([9.2, 123.55], 12);
      }
    }

    function lockProvince(islandBounds) {
      try {
        var padded = islandBounds.pad(-0.04);
        provinceBounds = padded;
        map.setMaxBounds(padded.pad(0.22));
        var minZ = map.getBoundsZoom(padded, false, fitPad);
        if (!isFinite(minZ) || minZ < 1) minZ = 11;
        map.setMinZoom(Math.max(10, Math.min(14, minZ)));
        map.fitBounds(padded, { padding: fitPad, maxZoom: 15 });
      } catch (e) {
        map.setView([9.2, 123.55], 12);
      }
    }

    function addLabels(geojson) {
      Object.keys(labelByName).forEach(function (k) {
        map.removeLayer(labelByName[k]);
      });
      labelByName = {};

      (geojson.features || []).forEach(function (feature) {
        var name = feature.properties && feature.properties.name;
        if (!name) return;
        var center;
        try {
          center = L.geoJSON(feature).getBounds().getCenter();
        } catch (e) {
          return;
        }
        var isFocusMuni =
          !isMuniFocus ||
          !focusMunicipalityName ||
          name.toLowerCase() === focusMunicipalityName.toLowerCase();
        var marker = L.marker(center, {
          interactive: !isMuniFocus,
          keyboard: false,
          zIndexOffset: isFocusMuni ? 700 : 500,
          icon: L.divIcon({
            className: 'rbim-map-label' + (isFocusMuni ? '' : ' rbim-map-label--muted'),
            html: '<span class="rbim-map-pill">' + shortName(name) + '</span>',
            iconSize: [0, 0],
            iconAnchor: [0, 0]
          })
        });
        if (!isMuniFocus) {
          marker.on('click', function () {
            focusMunicipality(name);
          });
        }
        marker.addTo(map);
        labelByName[name] = marker;
      });
    }

    if (floatCloseBtn) {
      floatCloseBtn.addEventListener('click', function () {
        closeFloat(true);
      });
    }

    if (resetBtn) {
      resetBtn.addEventListener('click', function () {
        closeFloat(true);
      });
    }

    function renderGeo(geojson) {
      if (!geojson || !geojson.features || !geojson.features.length) {
        throw new Error('Invalid map data');
      }

      var names = [];
      var focusFeature = null;
      if (isMuniFocus && focusMunicipalityName) {
        (geojson.features || []).forEach(function (feature) {
          var featureName = feature.properties && feature.properties.name;
          if (featureName && featureName.toLowerCase() === focusMunicipalityName.toLowerCase()) {
            focusFeature = feature;
            focusMunicipalityName = featureName;
          }
        });
        if (!focusFeature) {
          showMapError(canvas, 'Municipality map boundary not found.');
          return;
        }
        focusMunicipalityFeature = focusFeature;
      }

      L.geoJSON(geojson, {
        style: function (feature) {
          var name = feature.properties.name;
          names.push(name);
          var isFocusMuni =
            isMuniFocus &&
            name &&
            focusMunicipalityName &&
            name.toLowerCase() === focusMunicipalityName.toLowerCase();
          return styleFor(name, isFocusMuni);
        },
        onEachFeature: function (feature, featureLayer) {
          var name = feature.properties.name;
          layerByName[name] = featureLayer;
          if (isMuniFocus) {
            var isFocusMuni =
              name &&
              focusMunicipalityName &&
              name.toLowerCase() === focusMunicipalityName.toLowerCase();
            if (!isFocusMuni) {
              featureLayer.options.interactive = false;
            }
          } else {
            featureLayer.on({
              click: function () {
                focusMunicipality(name);
              }
            });
          }
        }
      }).addTo(map);

      if (isMuniFocus && focusFeature) {
        syncMunicipalityBorder(focusFeature);
      }

      var allBounds = L.geoJSON(geojson).getBounds();
      if (allBounds.isValid()) {
        if (isMuniFocus && focusFeature) {
          var muniBounds = L.geoJSON(focusFeature).getBounds();
          initMuniDashboardView(muniBounds);
        } else if (!isMuniFocus) {
          lockProvince(allBounds);
        }
      }

      addLabels(geojson);
      if (isMuniFocus) {
        if (legendSideTitleEl) legendSideTitleEl.textContent = 'Barangays';
        buildBarangayLegend(focusMunicipalityName);
        setActive(focusMunicipalityName);
        ensureBarangayGeo()
          .then(function () {
            showBarangayTiles(focusMunicipalityName);
            if (focusMunicipalityFeature) {
              syncMunicipalityBorder(focusMunicipalityFeature);
            }
            openFloat(focusMunicipalityName, layerByName[focusMunicipalityName]);
            scheduleMuniZoom();
          })
          .catch(function () {
            if (focusMunicipalityFeature) {
              syncMunicipalityBorder(focusMunicipalityFeature);
            }
            openFloat(focusMunicipalityName, layerByName[focusMunicipalityName]);
            scheduleMuniZoom();
          });
      } else {
        buildLegend(names.sort(function (a, b) {
          return a.localeCompare(b);
        }));
        updateSelectedCard(null);
      }

      canvas.classList.remove('is-loading');
      setTimeout(function () {
        map.invalidateSize();
        if (isMuniFocus) {
          scheduleMuniZoom();
        } else {
          try {
            if (provinceBounds && provinceBounds.isValid()) {
              map.fitBounds(provinceBounds, { padding: fitPad, maxZoom: 15 });
            }
          } catch (e) {}
        }
        raiseMunicipalityBorder();
      }, 120);

      if (!isMuniFocus) {
        ensureBarangayGeo().catch(function () {});
      }
    }

    function loadGeo() {
      var embedded = window.RBIM_SIQUIJOR_GEOJSON;
      if (embedded && embedded.features && embedded.features.length) {
        renderGeo(embedded);
        return;
      }

      if (!geoUrl) {
        showMapError(canvas, 'Map boundary data is missing.');
        return;
      }

      fetch(geoUrl, { credentials: 'same-origin' })
        .then(function (res) {
          if (!res.ok) throw new Error('HTTP ' + res.status);
          return res.json();
        })
        .then(renderGeo)
        .catch(function () {
          showMapError(canvas, 'Unable to load map boundary data.');
        });
    }

    try {
      loadGeo();
    } catch (e) {
      showMapError(canvas, 'Unable to load map.');
    }

    window.addEventListener('resize', function () {
      map.invalidateSize();
      if (floatEl && !floatEl.hidden) {
        placeFloat(floatEl.offsetLeft, floatEl.offsetTop, floatEl.offsetWidth, floatEl.offsetHeight);
      }
      try {
        if (isMuniFocus) {
          fitFocusMunicipality();
        } else if (!activeName && provinceBounds && provinceBounds.isValid()) {
          map.fitBounds(provinceBounds, { padding: fitPad, maxZoom: 15 });
        }
      } catch (e) {}
    });
  });
})();
