{{--
  Reusable "Stops" repeater for the route create/edit forms.
  Expects a $stops collection/array of ['name' => ..., 'eta' => ...].
  Submits as stop_name[] / stop_eta[] — order in the DOM becomes the
  stop sequence when RouteController@syncStops saves the route.
--}}
<div class="lf-group">
  <label class="lf-label">Stops *</label>
  <div id="stops-list">
    @forelse($stops as $s)
      <div class="stop-row" style="display:flex;gap:8px;margin-bottom:8px;align-items:center;">
        <div class="lf-input-wrap" style="flex:2;"><i class="fas fa-map-pin"></i>
          <input class="lf-input" type="text" name="stop_name[]" value="{{ is_array($s) ? ($s['name'] ?? '') : $s }}" placeholder="Stop name">
        </div>
        <div class="lf-input-wrap" style="flex:1;"><i class="fas fa-clock"></i>
          <input class="lf-input" type="text" name="stop_eta[]" value="{{ is_array($s) ? ($s['eta'] ?? '') : '' }}" placeholder="ETA (optional)">
        </div>
        <button type="button" class="btn btn-ERR btn-sm stop-remove" title="Remove stop"><i class="fas fa-trash"></i></button>
      </div>
    @empty
      <div class="stop-row" style="display:flex;gap:8px;margin-bottom:8px;align-items:center;">
        <div class="lf-input-wrap" style="flex:2;"><i class="fas fa-map-pin"></i>
          <input class="lf-input" type="text" name="stop_name[]" placeholder="Stop name">
        </div>
        <div class="lf-input-wrap" style="flex:1;"><i class="fas fa-clock"></i>
          <input class="lf-input" type="text" name="stop_eta[]" placeholder="ETA (optional)">
        </div>
        <button type="button" class="btn btn-ERR btn-sm stop-remove" title="Remove stop"><i class="fas fa-trash"></i></button>
      </div>
    @endforelse
  </div>
  <button type="button" id="stop-add" class="btn btn-S btn-sm"><i class="fas fa-plus"></i> Add Stop</button>
  <div style="font-size:11px;color:var(--color-text-faint);margin-top:6px;">
    Add every stop in the order the shuttle visits them. The order here sets the stop sequence.
  </div>
</div>

<script>
(function () {
  var list = document.getElementById('stops-list');
  var addBtn = document.getElementById('stop-add');
  if (!list || !addBtn) return;

  function rowTemplate() {
    var row = document.createElement('div');
    row.className = 'stop-row';
    row.style.cssText = 'display:flex;gap:8px;margin-bottom:8px;align-items:center;';
    row.innerHTML =
      '<div class="lf-input-wrap" style="flex:2;"><i class="fas fa-map-pin"></i>' +
      '<input class="lf-input" type="text" name="stop_name[]" placeholder="Stop name"></div>' +
      '<div class="lf-input-wrap" style="flex:1;"><i class="fas fa-clock"></i>' +
      '<input class="lf-input" type="text" name="stop_eta[]" placeholder="ETA (optional)"></div>' +
      '<button type="button" class="btn btn-ERR btn-sm stop-remove" title="Remove stop"><i class="fas fa-trash"></i></button>';
    return row;
  }

  addBtn.addEventListener('click', function () {
    list.appendChild(rowTemplate());
  });

  list.addEventListener('click', function (e) {
    var btn = e.target.closest('.stop-remove');
    if (!btn) return;
    var rows = list.querySelectorAll('.stop-row');
    if (rows.length > 1) {
      btn.closest('.stop-row').remove();
    } else {
      // Keep at least one row visible — just clear it instead of removing.
      btn.closest('.stop-row').querySelectorAll('input').forEach(function (i) { i.value = ''; });
    }
  });
})();
</script>
