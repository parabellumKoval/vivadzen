(function(window, document) {
  'use strict';

  var $ = window.jQuery || null;
  var pendingSatellites = [];
  var bindingUid = 0;
  var observerStarted = false;
  var matchesFn = Element.prototype.matches || Element.prototype.msMatchesSelector || Element.prototype.webkitMatchesSelector;

  function attrTruthy(value) {
    if (value == null) return null;
    var normalized = String(value).toLowerCase();
    if (normalized === 'true' || normalized === '1' || normalized === 'yes' || normalized === 'on') return true;
    if (normalized === 'false' || normalized === '0' || normalized === 'no' || normalized === 'off') return false;
    return normalized.length > 0;
  }

  function escapeAttrValue(value) {
    return String(value).replace(/\\/g, '\\\\').replace(/"/g, '\\"');
  }

  function elementMatches(el, selector) {
    if (!el || !selector) return false;
    if (matchesFn) return matchesFn.call(el, selector);
    var parent = el.parentNode || document;
    var nodes = parent.querySelectorAll(selector);
    for (var i = 0; i < nodes.length; i++) {
      if (nodes[i] === el) return true;
    }
    return false;
  }

  function closest(el, selector) {
    if (!el) return null;
    if (el.closest) return el.closest(selector);
    var current = el;
    while (current) {
      if (elementMatches(current, selector)) return current;
      current = current.parentElement;
    }
    return null;
  }

  function toValueArray(val) {
    if (val == null) return [];
    if (Array.isArray(val)) {
      var filtered = [];
      for (var i = 0; i < val.length; i++) {
        if (val[i] != null) filtered.push(String(val[i]));
      }
      return filtered;
    }
    if (typeof val === 'object' && typeof val.length === 'number') {
      var asArray = [];
      for (var j = 0; j < val.length; j++) {
        if (val[j] != null) asArray.push(String(val[j]));
      }
      return asArray;
    }
    return [String(val)];
  }

  function valueHasContent(val) {
    var arr = toValueArray(val);
    if (!arr.length) return false;
    for (var i = 0; i < arr.length; i++) {
      if (String(arr[i]).trim() !== '') return true;
    }
    return false;
  }

  function getValue(el) {
    if (!el) return null;
    if (el.type === 'checkbox') {
      if (el.name && el.name.slice(-2) === '[]') {
        var scope = el.form || document;
        var selector = 'input[type="checkbox"][name="' + escapeAttrValue(el.name) + '"]';
        var group = scope.querySelectorAll(selector);
        var checked = [];
        for (var c = 0; c < group.length; c++) {
          if (group[c].checked) checked.push(group[c].value || '1');
        }
        return checked;
      }
      return el.checked ? (el.value || '1') : '';
    }
    if (el.type === 'radio') {
      var form = el.form || document;
      var radios = form.querySelectorAll('input[type="radio"][name="' + escapeAttrValue(el.name) + '"]');
      for (var r = 0; r < radios.length; r++) {
        if (radios[r].checked) return radios[r].value;
      }
      return '';
    }
    if (el.tagName === 'SELECT' && el.multiple) {
      var selected = [];
      for (var i = 0; i < el.options.length; i++) {
        if (el.options[i].selected) selected.push(el.options[i].value);
      }
      return selected;
    }
    return el.value;
  }

  function getSelectedValues(selectEl, keepEmpty) {
    var values = [];
    if (!selectEl) return values;
    if (selectEl.tagName === 'SELECT') {
      for (var i = 0; i < selectEl.options.length; i++) {
        var option = selectEl.options[i];
        if (!option.selected) continue;
        if (!keepEmpty && (option.value == null || String(option.value) === '')) continue;
        values.push(String(option.value));
      }
    } else if ($) {
      var val = $(selectEl).val();
      if (Array.isArray(val)) values = val.slice();
      else if (val != null && (keepEmpty || String(val) !== '')) values = [val];
    } else {
      var single = selectEl.value;
      if (single != null && (keepEmpty || String(single) !== '')) values.push(String(single));
    }
    for (var j = 0; j < values.length; j++) {
      values[j] = String(values[j]);
    }
    return values;
  }

  function collectOptionLabels(selectEl) {
    var map = {};
    if (!selectEl || selectEl.tagName !== 'SELECT') return map;
    for (var i = 0; i < selectEl.options.length; i++) {
      var option = selectEl.options[i];
      map[String(option.value)] = option.text;
    }
    return map;
  }

  function hasSelect2Instance(el) {
    return !!($ && $(el).data && $(el).data('select2'));
  }

  function isSelect2(el) {
    if (!el) return false;
    if (hasSelect2Instance(el)) return true;
    if (el.classList && el.classList.contains('select2-hidden-accessible')) return true;
    var initFn = el.getAttribute && el.getAttribute('data-init-function');
    if (initFn && /^bpFieldInitSelect2/i.test(initFn)) return true;
    return false;
  }

  function isFormControl(el) {
    return elementMatches(el, 'select,input,textarea');
  }

  function addPending(el) {
    if (!el || el.__bp_dep_pending) return;
    el.__bp_dep_pending = true;
    pendingSatellites.push(el);
  }

  function removePending(el) {
    if (!el || !el.__bp_dep_pending) return;
    el.__bp_dep_pending = false;
    for (var i = pendingSatellites.length - 1; i >= 0; i--) {
      if (pendingSatellites[i] === el) {
        pendingSatellites.splice(i, 1);
        break;
      }
    }
  }

  function attemptPending() {
    if (!pendingSatellites.length) return;
    var queue = pendingSatellites.slice();
    pendingSatellites.length = 0;
    for (var i = 0; i < queue.length; i++) {
      var el = queue[i];
      if (!el || !el.isConnected) {
        if (el) el.__bp_dep_pending = false;
        continue;
      }
      el.__bp_dep_pending = false;
      bindOne(el);
    }
  }

  function normalizeOptionsList(raw, cfg) {
    if (raw == null) return [];
    if (typeof raw === 'string') {
      try { raw = JSON.parse(raw); }
      catch (e) { return []; }
    }
    if (typeof raw === 'object' && !Array.isArray(raw) && Array.isArray(raw.data)) {
      raw = raw.data;
    }
    var list = [];
    if (Array.isArray(raw)) {
      for (var i = 0; i < raw.length; i++) {
        var item = raw[i];
        if (Array.isArray(item) && item.length >= 2) {
          list.push({ value: String(item[0]), label: String(item[1]) });
          continue;
        }
        if (item && typeof item === 'object') {
          var valueKey = null;
          if (item.hasOwnProperty('id')) valueKey = item.id;
          else if (item.hasOwnProperty('value')) valueKey = item.value;
          else if (item.hasOwnProperty('key')) valueKey = item.key;
          if (valueKey == null) continue;
          var label = null;
          if (cfg && cfg.labelProp && item[cfg.labelProp] != null) label = item[cfg.labelProp];
          else if (item.hasOwnProperty('text')) label = item.text;
          else if (item.hasOwnProperty('label')) label = item.label;
          else if (item.hasOwnProperty('name')) label = item.name;
          else if (item.hasOwnProperty('title')) label = item.title;
          if (label == null) label = valueKey;
          list.push({ value: String(valueKey), label: String(label) });
        }
      }
      return list;
    }
    if (typeof raw === 'object') {
      for (var key in raw) {
        if (!raw.hasOwnProperty(key)) continue;
        var labelVal = raw[key];
        if (labelVal == null) continue;
        if (typeof labelVal === 'object') {
          var labelText = null;
          if (labelVal.hasOwnProperty('text')) labelText = labelVal.text;
          else if (labelVal.hasOwnProperty('label')) labelText = labelVal.label;
          else if (labelVal.hasOwnProperty('name')) labelText = labelVal.name;
          else if (labelVal.hasOwnProperty('title')) labelText = labelVal.title;
          else labelText = JSON.stringify(labelVal);
          list.push({ value: String(key), label: String(labelText) });
        } else {
          list.push({ value: String(key), label: String(labelVal) });
        }
      }
      return list;
    }
    return list;
  }

  function parseMapAttribute(mapJson, cfg) {
    if (!mapJson) return null;
    var raw;
    try { raw = JSON.parse(mapJson); }
    catch (e) {
      if (typeof console !== 'undefined' && console.warn) {
        console.warn('Backpack dependent fields: invalid data-dep-map JSON', e);
      }
      return null;
    }
    if (!raw || typeof raw !== 'object') return null;
    var entries = [];
    for (var key in raw) {
      if (!raw.hasOwnProperty(key)) continue;
      var entry = { type: 'exact', values: [], optionList: normalizeOptionsList(raw[key], cfg) };
      if (key === '*') {
        entry.type = 'wildcard';
      } else if (key.charAt(0) === '[' && key.charAt(key.length - 1) === ']') {
        var inner = key.slice(1, -1).split(',');
        var values = [];
        for (var i = 0; i < inner.length; i++) {
          var trimmed = String(inner[i]).trim();
          if (trimmed.length) values.push(trimmed);
        }
        entry.type = 'set';
        entry.values = values;
      } else {
        entry.values = [String(key)];
      }
      entries.push(entry);
    }
    return entries;
  }

  function appendOptions(targetList, sourceList, seen) {
    if (!sourceList || !sourceList.length) return;
    for (var i = 0; i < sourceList.length; i++) {
      var item = sourceList[i];
      if (!item) continue;
      var value = String(item.value);
      if (seen[value]) continue;
      seen[value] = true;
      var label = item.label != null ? String(item.label) : value;
      targetList.push({ value: value, label: label });
    }
  }

  function resolveMapOptions(entries, driverValue) {
    if (!entries || !entries.length) return [];
    var values = toValueArray(driverValue);
    var list = [];
    var seen = {};
    var matched = false;

    if (values.length) {
      for (var i = 0; i < entries.length; i++) {
        var entry = entries[i];
        if (entry.type === 'wildcard') continue;
        var matches = false;
        for (var v = 0; v < values.length && !matches; v++) {
          var val = values[v];
          if (entry.type === 'exact') matches = entry.values.indexOf(val) !== -1;
          else if (entry.type === 'set') matches = entry.values.indexOf(val) !== -1;
        }
        if (matches) {
          appendOptions(list, entry.optionList, seen);
          matched = true;
        }
      }
    }

    if (!matched) {
      for (var j = 0; j < entries.length; j++) {
        var wildcard = entries[j];
        if (wildcard.type !== 'wildcard') continue;
        appendOptions(list, wildcard.optionList, seen);
        matched = true;
      }
    }

    if (!matched && !values.length) {
      for (var k = 0; k < entries.length; k++) {
        var emptyMatch = entries[k];
        if (emptyMatch.type === 'exact' && emptyMatch.values.indexOf('') !== -1) {
          appendOptions(list, emptyMatch.optionList, seen);
          matched = true;
        }
      }
    }

    return list;
  }

  function fetchOptionsByUrl(urlTemplate, driverValue, cfg, done) {
    var values = toValueArray(driverValue).join(',');
    var encoded = encodeURIComponent(values);
    var endpoint = urlTemplate;
    if (urlTemplate && urlTemplate.indexOf('{value}') !== -1) {
      endpoint = urlTemplate.split('{value}').join(encoded);
    }
    var handle = function(resp) { done(null, normalizeOptionsList(resp, cfg)); };
    var handleError = function(err) { done(err || true, []); };

    if ($ && $.ajax) {
      $.ajax({ url: endpoint, method: 'GET', dataType: 'json' }).done(handle).fail(handleError);
    } else if (window.fetch) {
      window.fetch(endpoint, { credentials: 'same-origin' })
        .then(function(res) { return res.json(); })
        .then(handle)
        .catch(handleError);
    } else {
      handleError(true);
    }
  }

  function renderOptions(selectEl, optionList, cfg) {
    if (!selectEl || selectEl.tagName !== 'SELECT') return;
    var allowNull = !!cfg.allowNull;
    var placeholder = cfg.placeholder != null ? String(cfg.placeholder) : '';
    var autoselect = !!cfg.autoselect;
    var keepStale = cfg.keepStale !== false;

    var existingLabels = collectOptionLabels(selectEl);
    var selectedValues = getSelectedValues(selectEl, allowNull);
    var selectedMap = {};
    for (var i = 0; i < selectedValues.length; i++) {
      selectedMap[selectedValues[i]] = true;
    }

    var frag = document.createDocumentFragment();

    if (allowNull) {
      var placeholderSelected = selectedMap.hasOwnProperty('');
      if (placeholderSelected) delete selectedMap[''];
      var placeholderOption = new Option(placeholder, '', placeholderSelected, placeholderSelected);
      placeholderOption.setAttribute('data-bp-dep-placeholder', '1');
      frag.appendChild(placeholderOption);
    }

    var seen = {};
    for (var j = 0; j < optionList.length; j++) {
      var item = optionList[j];
      if (!item) continue;
      var value = String(item.value);
      if (seen[value]) continue;
      seen[value] = true;
      var label = item.label != null ? String(item.label) : value;
      var isSelected = selectedMap.hasOwnProperty(value);
      if (isSelected) delete selectedMap[value];
      var option = new Option(label, value, isSelected, isSelected);
      frag.appendChild(option);
    }

    if (keepStale) {
      for (var key in selectedMap) {
        if (!selectedMap.hasOwnProperty(key)) continue;
        if (key === '' && allowNull) continue;
        var staleLabel = existingLabels.hasOwnProperty(key) ? existingLabels[key] : key;
        var stale = new Option(staleLabel, key, true, true);
        stale.setAttribute('data-bp-dep-stale', '1');
        frag.appendChild(stale);
      }
    }

    while (selectEl.options.length) {
      selectEl.remove(0);
    }
    selectEl.appendChild(frag);

    if (autoselect && !selectEl.multiple) {
      var hasSelection = false;
      for (var idx = 0; idx < selectEl.options.length; idx++) {
        var opt = selectEl.options[idx];
        if (opt.selected && (!allowNull || opt.value !== '')) {
          hasSelection = true;
          break;
        }
      }
      if (!hasSelection) {
        var startIndex = allowNull ? 1 : 0;
        if (selectEl.options.length - startIndex === 1) {
          var autoOption = selectEl.options[startIndex];
          if (autoOption && !autoOption.disabled) {
            autoOption.selected = true;
          }
        }
      }
    }

    if ($) {
      var $el = $(selectEl);
      $el.trigger('change.select2');
      $el.trigger('change');
    } else {
      var evt = document.createEvent('HTMLEvents');
      evt.initEvent('change', true, false);
      selectEl.dispatchEvent(evt);
    }
  }

  function resolveDriver(targetEl, dependsOn, scope) {
    scope = scope || 'auto';
    var names = [dependsOn];
    if (dependsOn && dependsOn.slice(-2) === '[]') {
      names.push(dependsOn.slice(0, -2));
    }

    var selectorParts = [];
    for (var i = 0; i < names.length; i++) {
      var name = names[i];
      if (!name) continue;
      selectorParts.push('[name="' + escapeAttrValue(name) + '"]');
      selectorParts.push('[name="' + escapeAttrValue(name) + '[]"]');
      selectorParts.push('[data-repeatable-input-name="' + escapeAttrValue(name) + '"]');
      selectorParts.push('[data-repeatable-input-name="' + escapeAttrValue(name) + '[]"]');
      selectorParts.push('[data-dep-source="' + escapeAttrValue(name) + '"]');
      selectorParts.push('[data-field-name="' + escapeAttrValue(name) + '"]');
    }

    var selector = selectorParts.join(',');

    function locate(root) {
      if (!root || !root.querySelectorAll || !selector) return null;
      var found = root.querySelectorAll(selector);
      for (var idx = 0; idx < found.length; idx++) {
        var node = found[idx];
        if (isFormControl(node)) return node;
        if (node.querySelector) {
          var inner = node.querySelector('select,input,textarea');
          if (inner) return inner;
        }
      }
      return null;
    }

    if (scope && scope !== 'auto' && scope !== 'row' && scope !== 'global') {
      var customRoot = null;
      try { customRoot = closest(targetEl, scope); }
      catch (err) { customRoot = null; }
      if (customRoot) {
        var withinCustom = locate(customRoot);
        if (withinCustom) return withinCustom;
      }
    }

    if (scope !== 'global') {
      var repeatableRow = closest(targetEl, '.repeatable-element');
      if (repeatableRow) {
        var withinRow = locate(repeatableRow);
        if (withinRow) return withinRow;
      }
      if (scope === 'row') return null;
    }

    var form = closest(targetEl, 'form');
    if (form) {
      var withinForm = locate(form);
      if (withinForm) return withinForm;
    }

    return locate(document);
  }

  function buildConfig(targetEl) {
    var cfg = {};
    var allowAttr = targetEl.getAttribute('data-allow-null');
    var placeholderOption = targetEl.querySelector ? targetEl.querySelector('option[value=""]') : null;
    cfg.allowNull = allowAttr !== null ? !!attrTruthy(allowAttr) : !!placeholderOption;

    var placeholderAttr = targetEl.getAttribute('data-placeholder');
    if (placeholderAttr != null) cfg.placeholder = placeholderAttr;
    else cfg.placeholder = placeholderOption ? placeholderOption.text : '';

    var autoselectAttr = targetEl.getAttribute('data-dep-autoselect');
    cfg.autoselect = autoselectAttr != null ? !!attrTruthy(autoselectAttr) : false;

    var keepAttr = targetEl.getAttribute('data-dep-keep-stale');
    cfg.keepStale = keepAttr != null ? !!attrTruthy(keepAttr) : true;

    cfg.labelProp = targetEl.getAttribute('data-label-prop') || null;

    var mapAttr = targetEl.getAttribute('data-dep-map');
    if (mapAttr) cfg.mapEntries = parseMapAttribute(mapAttr, cfg);

    var urlAttr = targetEl.getAttribute('data-dep-url');
    if (urlAttr) cfg.url = urlAttr;

    return cfg;
  }

  function makeUpdater(targetEl, cfg) {
    var requestToken = 0;
    return function(driverEl) {
      if (cfg.mapEntries && cfg.mapEntries.length) {
        var options = resolveMapOptions(cfg.mapEntries, getValue(driverEl));
        renderOptions(targetEl, options, cfg);
        return;
      }

      if (cfg.url) {
        var driverValue = getValue(driverEl);
        if (!valueHasContent(driverValue)) {
          renderOptions(targetEl, [], cfg);
          return;
        }
        var token = ++requestToken;
        fetchOptionsByUrl(cfg.url, driverValue, cfg, function(_err, options) {
          if (token !== requestToken) return;
          renderOptions(targetEl, options, cfg);
        });
        return;
      }

      renderOptions(targetEl, [], cfg);
    };
  }

  function detachDriver(meta) {
    if (!meta) return;
    if ($ && meta.$driver && meta.eventNamespace) {
      meta.$driver.off(meta.eventNamespace);
    }
    if (meta.nativeDriver && meta.nativeHandler) {
      meta.nativeDriver.removeEventListener('change', meta.nativeHandler);
      meta.nativeDriver.removeEventListener('input', meta.nativeHandler);
    }
    meta.$driver = null;
    meta.nativeDriver = null;
    meta.nativeHandler = null;
    meta.eventNamespace = null;
  }

  function attachDriver(meta) {
    if (!meta || !meta.driver || !meta.updater) return;
    var driver = meta.driver;
    var handler = function() {
      meta.updater(driver);
    };

    if ($) {
      var $driver = $(driver);
      var ns = '.bpDependentOptions' + meta.id;
      if (meta.$driver && meta.eventNamespace && meta.$driver[0] !== driver) {
        meta.$driver.off(meta.eventNamespace);
      }
      meta.$driver = $driver;
      meta.eventNamespace = ns;
      $driver.off(ns);
      $driver.on('change' + ns, handler);
      if (isSelect2(driver)) {
        $driver.on('select2:select' + ns + ' select2:unselect' + ns + ' select2:clear' + ns, handler);
      } else if (driver.tagName === 'SELECT') {
        $driver.on('input' + ns, handler);
      }
    } else {
      if (meta.nativeDriver && meta.nativeHandler && meta.nativeDriver !== driver) {
        meta.nativeDriver.removeEventListener('change', meta.nativeHandler);
        meta.nativeDriver.removeEventListener('input', meta.nativeHandler);
      }
      meta.nativeDriver = driver;
      meta.nativeHandler = handler;
      driver.addEventListener('change', handler);
      driver.addEventListener('input', handler);
    }

    meta.updater(driver);
  }

  function bindOne(targetEl) {
    if (!targetEl || targetEl.nodeType !== 1) return;
    var dependsOn = targetEl.getAttribute('data-depends-on');
    if (!dependsOn) return;

    var meta = targetEl.__bp_dep_meta;
    if (!meta) {
      meta = { id: ++bindingUid, target: targetEl };
      targetEl.__bp_dep_meta = meta;
    }

    if (!meta.config) {
      meta.config = buildConfig(targetEl);
    }

    if (!meta.updater) {
      meta.updater = makeUpdater(targetEl, meta.config);
    }

    var scope = targetEl.getAttribute('data-dep-scope') || 'auto';
    var driver = resolveDriver(targetEl, dependsOn, scope);

    if (!driver || !driver.isConnected) {
      addPending(targetEl);
      meta.bound = false;
      return;
    }

    removePending(targetEl);

    if (meta.bound && meta.driver === driver) {
      meta.updater(driver);
      return;
    }

    if (meta.driver && meta.driver !== driver) {
      detachDriver(meta);
    }

    meta.driver = driver;
    attachDriver(meta);
    meta.bound = true;
  }

  function toArray(value) {
    if (!value) return [];
    if (value.jquery) return value.toArray();
    if (Array.isArray(value)) return value;
    if (window.NodeList && value instanceof NodeList) return Array.prototype.slice.call(value);
    return [value];
  }

  function bindAll(ctx) {
    var roots = ctx ? toArray(ctx) : [document];
    for (var r = 0; r < roots.length; r++) {
      var root = roots[r];
      if (!root) continue;
      var candidates = [];
      if (root.nodeType === 1) {
        var initFunction = root.getAttribute('data-init-function');
        var marker = root.getAttribute('data-bp-dependent-options');
        if (initFunction === 'bpFieldInitDependentOptions' || marker === '1') {
          candidates.push(root);
        }
      }
      if (root.querySelectorAll) {
        var found = root.querySelectorAll('[data-init-function="bpFieldInitDependentOptions"],[data-bp-dependent-options="1"]');
        for (var i = 0; i < found.length; i++) {
          candidates.push(found[i]);
        }
      }
      for (var c = 0; c < candidates.length; c++) {
        var node = candidates[c];
        var target = node;
        if (!isFormControl(node)) {
          if (node.querySelector) target = node.querySelector('select,input,textarea');
        }
        if (target) bindOne(target);
      }
    }
    attemptPending();
  }

  function startObserver() {
    if (observerStarted) return;
    if (typeof MutationObserver !== 'function') return;
    var target = document.body || document.documentElement;
    if (!target) return;
    var observer = new MutationObserver(function(mutations) {
      for (var i = 0; i < mutations.length; i++) {
        var mutation = mutations[i];
        if (mutation.addedNodes && mutation.addedNodes.length) {
          bindAll(mutation.addedNodes);
        }
        if (mutation.removedNodes && mutation.removedNodes.length) {
          // Removing nodes may remove drivers; re-check pending satellites.
          attemptPending();
        }
      }
    });
    observer.observe(target, { childList: true, subtree: true });
    observerStarted = true;
  }

  window.bpFieldInitDependentOptions = function(element) {
    bindAll(element || document);
  };

  function initialize() {
    bindAll();
    startObserver();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initialize);
  } else {
    initialize();
  }

})(window, document);
