import React, { useState, useCallback, useEffect, useRef } from 'react';
import { Odontogram } from 'react-odontogram';

// Map FDI notation (11-48) to MdSmile tooth_number (1-32). Order: 11–18 upper right, 21–28 upper left, 31–38 lower left, 41–48 lower right.
const FDI_TO_OURS = {
  11: 1, 12: 2, 13: 3, 14: 4, 15: 5, 16: 6, 17: 7, 18: 8,
  21: 9, 22: 10, 23: 11, 24: 12, 25: 13, 26: 14, 27: 15, 28: 16,
  31: 17, 32: 18, 33: 19, 34: 20, 35: 21, 36: 22, 37: 23, 38: 24,
  41: 25, 42: 26, 43: 27, 44: 28, 45: 29, 46: 30, 47: 31, 48: 32,
};

const OURS_TO_FDI = Object.fromEntries(
  Object.entries(FDI_TO_OURS).map(([fdi, ours]) => [ours, parseInt(fdi, 10)])
);

// react-odontogram id format: teeth-{quadrant}{tooth} -> Universal 1-32
const ID_TO_OURS = {
  11: 1, 12: 2, 13: 3, 14: 4, 15: 5, 16: 6, 17: 7, 18: 8,
  21: 9, 22: 10, 23: 11, 24: 12, 25: 13, 26: 14, 27: 15, 28: 16,
  31: 17, 32: 18, 33: 19, 34: 20, 35: 21, 36: 22, 37: 23, 38: 24,
  41: 25, 42: 26, 43: 27, 44: 28, 45: 29, 46: 30, 47: 31, 48: 32,
};

// react-odontogram id = FDI: teeth-11 … teeth-48 in standard order (11–18 upper right, 21–28 upper left, 31–38 lower left, 41–48 lower right)
const ID_TO_FDI = {
  11: 11, 12: 12, 13: 13, 14: 14, 15: 15, 16: 16, 17: 17, 18: 18,
  21: 21, 22: 22, 23: 23, 24: 24, 25: 25, 26: 26, 27: 27, 28: 28,
  31: 31, 32: 32, 33: 33, 34: 34, 35: 35, 36: 36, 37: 37, 38: 38,
  41: 41, 42: 42, 43: 43, 44: 44, 45: 45, 46: 46, 47: 47, 48: 48,
};

// Display fix requested by clinic: lower labels mirrored left/right
// so 31-38 appear on the right side and 41-48 on the left side.
const DISPLAY_FDI = {
  31: 41, 32: 42, 33: 43, 34: 44, 35: 45, 36: 46, 37: 47, 38: 48,
  41: 31, 42: 32, 43: 33, 44: 34, 45: 35, 46: 36, 47: 37, 48: 38,
};

function getFdiFromSelection(t) {
  const fdi = t.notations?.fdi;
  if (fdi != null) return parseInt(fdi, 10);
  const num = parseInt(t.id.replace('teeth-', ''), 10);
  return ID_TO_FDI[num] ?? num;
}

// Palette for assigned teeth - each assignment gets a different color
const ASSIGNED_COLORS = [
  '#10b981', // emerald
  '#3b82f6', // blue
  '#8b5cf6', // violet
  '#f59e0b', // amber
  '#ec4899', // pink
  '#06b6d4', // cyan
  '#84cc16', // lime
];

// Build initial teeth state: [{ tooth_number, stock_id?, stock_name?, prestation_id?, prestation_name? }]
function buildInitialTeeth(initialTeeth = [], stockMap = {}) {
  const teeth = {};
  const materialToColorIndex = {};
  let nextColorIndex = 0;
  (initialTeeth || []).forEach(({ tooth_number, stock_id, stock_name, prestation_id, prestation_name }) => {
    const fdi = OURS_TO_FDI[tooth_number];
    if (fdi == null) return;
    const name = prestation_name || stock_name || (stock_id != null ? stockMap[String(stock_id)]?.name : null) || 'Dent';
    const key = prestation_id != null ? `prestation_${prestation_id}` : (stock_id != null ? `id_${stock_id}` : 'teeth_only');
    if (!(key in materialToColorIndex)) {
      materialToColorIndex[key] = nextColorIndex % ASSIGNED_COLORS.length;
      nextColorIndex += 1;
    }
    teeth[fdi] = {
      stock_id: stock_id != null ? String(stock_id) : null,
      prestation_id: prestation_id != null ? prestation_id : null,
      name,
      colorIndex: materialToColorIndex[key],
    };
  });
  return teeth;
}

export default function OdontogramApp({ stockItems = [], initialTeeth = [], prestations = [] }) {
  const stockMap = Object.fromEntries(
    (stockItems || []).map((s) => [String(s.id), { id: s.id, name: s.name }])
  );
  const [selectedIds, setSelectedIds] = useState([]);
  const [teeth, setTeeth] = useState(() => buildInitialTeeth(initialTeeth, stockMap));
  const [pendingMaterial, setPendingMaterial] = useState('');
  const [pendingPrestation, setPendingPrestation] = useState('');
  const [toothPositions, setToothPositions] = useState([]); // { id, number, x, y }[]
  const [odontogramKey, setOdontogramKey] = useState(0);
  const odontogramRef = useRef(null);
  const odontogramContainerRef = useRef(null);

  const removeTooth = useCallback((fdi) => {
    setTeeth((prev) => {
      const next = { ...prev };
      delete next[fdi];
      return next;
    });
  }, []);

  const handleOdontogramChange = useCallback((selected) => {
    setSelectedIds(selected.map((t) => t.id));
  }, []);

  const handleOdontogramChangeWrapper = useCallback(
    (selected) => handleOdontogramChange(selected),
    [handleOdontogramChange]
  );

  const applyMaterial = useCallback(() => {
    if (!pendingMaterial || selectedIds.length === 0) return;
    const item = stockMap[pendingMaterial];
    if (!item) return;
    // Same material = same color: reuse colorIndex if this material is already on any tooth
    const existing = Object.values(teeth).find(
      (d) => d && String(d.stock_id) === String(pendingMaterial)
    );
    const colorIndex =
      existing != null
        ? existing.colorIndex
        : (Math.max(-1, ...Object.values(teeth).map((d) => (d.colorIndex ?? -1))) + 1) %
          ASSIGNED_COLORS.length;
    const next = { ...teeth };
    selectedIds.forEach((id) => {
      const fdi = getFdiFromSelection({ id });
      next[fdi] = {
        stock_id: pendingMaterial,
        name: item.name,
        colorIndex,
      };
    });
    setTeeth(next);
    setSelectedIds([]);
    setOdontogramKey((k) => k + 1);
  }, [pendingMaterial, selectedIds, stockMap, teeth]);

  // Measure tooth positions - same logic as OdontogramShow for identical size/positioning
  useEffect(() => {
    const measure = () => {
      const container = odontogramContainerRef.current;
      if (!container) return;
      const svg = container.querySelector('.Odontogram svg');
      if (!svg) return;
      const containerRect = container.getBoundingClientRect();
      const w = containerRect.width;
      const h = containerRect.height;
      if (!w || !h) return;
      const groups = svg.querySelectorAll('g[class*="teeth-"]');
      const positions = [];
      groups.forEach((g) => {
        const cls = typeof g.className === 'string' ? g.className : (g.className?.baseVal ?? '');
        const match = cls.match(/teeth-(\d+)/);
        if (!match) return;
        const num = parseInt(match[1], 10);
        const ours = ID_TO_OURS[num];
        if (ours == null) return;
        // Use outline path (first path) - matches actual tooth shape, avoids gap/misclassification between 22/21, 30/29 etc
        const outlinePath = g.querySelector('path');
        const rect = outlinePath ? outlinePath.getBoundingClientRect() : g.getBoundingClientRect();
        const centerX = rect.left - containerRect.left + rect.width / 2;
        const centerY = rect.top - containerRect.top + rect.height / 2;
        const yNudge = rect.height * 0.06;
        const xPercent = (centerX / w) * 100;
        const yPercent = ((centerY - yNudge) / h) * 100;
        positions.push({
          id: `teeth-${match[1]}`,
          number: DISPLAY_FDI[ID_TO_FDI[num]] ?? ID_TO_FDI[num] ?? ours,
          xPercent,
          yPercent,
        });
      });
      setToothPositions(positions);
    };
    const t = setTimeout(measure, 150);
    const container = odontogramContainerRef.current;
    const ro = container ? new ResizeObserver(measure) : null;
    if (ro && container) ro.observe(container);
    const onBeforePrint = () => setTimeout(measure, 50);
    window.addEventListener('beforeprint', onBeforePrint);
    return () => {
      clearTimeout(t);
      ro?.disconnect();
      window.removeEventListener('beforeprint', onBeforePrint);
    };
  }, [selectedIds, teeth]);

  // Mark assigned teeth (different color per assignment)
  useEffect(() => {
    const container = odontogramRef.current;
    if (!container) return;
    const svg = container.querySelector('.Odontogram svg');
    if (!svg) return;
    svg.querySelectorAll('g[class*="teeth-"]').forEach((g) => {
      const cls = typeof g.className === 'string' ? g.className : (g.className?.baseVal ?? '');
      const match = cls.match(/teeth-(\d+)/);
      if (!match) return;
      const id = parseInt(match[1], 10);
      const fdi = ID_TO_FDI[id];
      const data = teeth[fdi];
      g.classList.remove('assigned', 'assigned-0', 'assigned-1', 'assigned-2', 'assigned-3', 'assigned-4', 'assigned-5', 'assigned-6');
      g.style.removeProperty('--assigned-fill');
      if (data) {
        g.classList.add('assigned');
        const ci = (data.colorIndex ?? 0) % ASSIGNED_COLORS.length;
        g.classList.add(`assigned-${ci}`);
        g.style.setProperty('--assigned-fill', ASSIGNED_COLORS[ci]);
      }
    });
  }, [teeth]);

  // Notify parent form: teeth list with prestation_id for total price calculation
  useEffect(() => {
    const count = Object.keys(teeth).length;
    const teethList = Object.entries(teeth).map(([fdi, data]) => ({
      tooth_number: FDI_TO_OURS[parseInt(fdi, 10)],
      prestation_id: data.prestation_id != null && data.prestation_id !== '' ? data.prestation_id : null,
    })).filter((t) => t.tooth_number != null);
    window.MDSMILE_TEETH_COUNT = count;
    window.MDSMILE_TEETH_LIST = teethList;
    window.dispatchEvent(new CustomEvent('teeth-count-changed', { detail: { count, teeth: teethList } }));
    if (typeof window.MDSMILE_UPDATE_PRIX === 'function') {
      window.MDSMILE_UPDATE_PRIX();
    }
  }, [teeth]);

  const teethOnlyMode = (stockItems || []).length === 0;
  const prestationMode = teethOnlyMode && (prestations || []).length > 0;

  const applyPrestationToSelectedTeeth = useCallback(() => {
    if (selectedIds.length === 0 || !pendingPrestation) return;
    const prest = (prestations || []).find((p) => String(p.id) === String(pendingPrestation));
    if (!prest) return;
    const next = { ...teeth };
    const existing = Object.values(teeth).find((d) => d && d.prestation_id != null && String(d.prestation_id) === String(pendingPrestation));
    const colorIndex = existing != null ? existing.colorIndex : (Math.max(-1, ...Object.values(teeth).map((d) => (d && d.colorIndex != null ? d.colorIndex : -1))) + 1) % ASSIGNED_COLORS.length;
    selectedIds.forEach((id) => {
      const fdi = getFdiFromSelection({ id });
      next[fdi] = { stock_id: null, prestation_id: prest.id, name: prest.name, colorIndex };
    });
    setTeeth(next);
    setSelectedIds([]);
    setPendingPrestation('');
    setOdontogramKey((k) => k + 1);
  }, [selectedIds, teeth, pendingPrestation, prestations]);

  const addSelectedTeethNoPrestation = useCallback(() => {
    if (selectedIds.length === 0) return;
    const next = { ...teeth };
    const colorIndex = (Math.max(-1, ...Object.values(teeth).map((d) => (d && d.colorIndex != null ? d.colorIndex : -1))) + 1) % ASSIGNED_COLORS.length;
    selectedIds.forEach((id) => {
      const fdi = getFdiFromSelection({ id });
      next[fdi] = { stock_id: null, prestation_id: null, name: 'Dent', colorIndex };
    });
    setTeeth(next);
    setSelectedIds([]);
    setOdontogramKey((k) => k + 1);
  }, [selectedIds, teeth]);

  return (
    <div className="space-y-4">
      {/* Prestation list: select teeth then apply a prestation to them */}
      {selectedIds.length > 0 && (
        <div className="mb-6 flex flex-wrap items-end gap-4 rounded-lg border border-[#967A4B]/30 bg-zinc-800/50 p-4">
          {!teethOnlyMode ? (
            <>
              <div className="min-w-[200px] flex-1">
                <label className="mb-1 block text-sm font-medium text-zinc-400">
                  Matériau (stock)
                </label>
                <select
                  value={pendingMaterial}
                  onChange={(e) => setPendingMaterial(e.target.value)}
                  className="auth-input w-full rounded-lg border border-zinc-600 bg-zinc-800/90 px-4 py-2.5 text-zinc-100 focus:border-[#967A4B] focus:outline-none focus:ring-1 focus:ring-[#967A4B]/30"
                >
                  <option value="">— Choisir —</option>
                  {(stockItems || []).map((item) => (
                    <option key={item.id} value={item.id}>
                      {item.name}
                    </option>
                  ))}
                </select>
              </div>
              <button
                type="button"
                onClick={applyMaterial}
                className="rounded-lg border border-[#967A4B] bg-[#967A4B] px-4 py-2.5 text-sm font-medium text-black hover:bg-[#B8986B]"
              >
                Appliquer aux {selectedIds.length} dent(s)
              </button>
            </>
          ) : prestationMode ? (
            <>
              <div className="min-w-[240px] flex-1">
                <label className="mb-1 block text-sm font-medium text-zinc-400">
                  Prestation
                </label>
                <select
                  value={pendingPrestation}
                  onChange={(e) => setPendingPrestation(e.target.value)}
                  className="auth-input w-full rounded-lg border border-zinc-600 bg-zinc-800/90 px-4 py-2.5 text-zinc-100 focus:border-[#967A4B] focus:outline-none focus:ring-1 focus:ring-[#967A4B]/30"
                >
                  <option value="">— Choisir une prestation —</option>
                  {(prestations || []).map((item) => (
                    <option key={item.id} value={item.id}>
                      {item.categoryName ? item.categoryName + ' — ' : ''}{item.name}
                    </option>
                  ))}
                </select>
              </div>
              <button
                type="button"
                onClick={applyPrestationToSelectedTeeth}
                disabled={!pendingPrestation}
                className="rounded-lg border border-[#967A4B] bg-[#967A4B] px-4 py-2.5 text-sm font-medium text-black hover:bg-[#B8986B] disabled:opacity-50 disabled:cursor-not-allowed"
              >
                Appliquer aux {selectedIds.length} dent(s)
              </button>
            </>
          ) : (
            <button
              type="button"
              onClick={addSelectedTeethNoPrestation}
              className="rounded-lg border border-[#967A4B] bg-[#967A4B] px-4 py-2.5 text-sm font-medium text-black hover:bg-[#B8986B]"
            >
              Ajouter les {selectedIds.length} dent(s) concernées
            </button>
          )}
          <button
            type="button"
            onClick={() => setSelectedIds([])}
            className="rounded-lg border border-zinc-600 px-4 py-2.5 text-sm font-medium text-zinc-400 hover:bg-zinc-700"
          >
            Annuler sélection
          </button>
        </div>
      )}

      {/* Odontogram (react-odontogram) */}
      <div
        ref={odontogramRef}
        className="odontogram-wrapper rounded-lg border border-zinc-700/50 bg-zinc-950/50 p-6"
        style={{
          '--dark-blue': '#967A4B',
          '--base-blue': '#71717a',
          '--light-blue': '#B8986B',
        }}
      >
        <p className="mb-4 text-center text-sm text-zinc-500">
          {prestationMode
            ? 'Cliquez sur les dents concernées, choisissez une prestation dans la liste ci-dessus, puis « Appliquer aux X dent(s) ».'
            : teethOnlyMode
              ? 'Cliquez sur les dents concernées puis « Ajouter les X dent(s) concernées ».'
              : 'Cliquez sur une dent pour la sélectionner, puis choisissez le matériau'}
        </p>
        <div
          ref={odontogramContainerRef}
          className="relative mx-auto max-w-2xl"
        >
          <Odontogram
            key={odontogramKey}
            defaultSelected={selectedIds}
            onChange={handleOdontogramChangeWrapper}
            theme="dark"
            notation="Universal"
            className="odontogram-mdsmile"
            colors={{
              darkBlue: '#967A4B',
              baseBlue: '#71717a',
              lightBlue: '#B8986B',
            }}
            showTooltip={true}
          />
          {/* Number overlay - same structure as Fiche Détail Travail (OdontogramShow) */}
          <div
            className="pointer-events-none absolute inset-0"
            aria-hidden
          >
            <div className="absolute inset-0">
              {toothPositions.map(({ number, xPercent, yPercent }) => (
                <span
                  key={number}
                  className="absolute -translate-x-1/2 -translate-y-1/2 text-xs font-bold text-white drop-shadow-[0_1px_1px_rgba(0,0,0,0.9)] print:drop-shadow-[0_0_1px_black]"
                  style={{ left: `${xPercent}%`, top: `${yPercent}%` }}
                >
                  {number}
                </span>
              ))}
            </div>
          </div>
        </div>

        {/* Material labels for assigned teeth - click X to remove */}
        {Object.keys(teeth).length > 0 && (
          <div className="mt-4 flex flex-wrap justify-center gap-2">
            {Object.entries(teeth).map(([fdi, data]) => {
              const ours = FDI_TO_OURS[parseInt(fdi, 10)];
              const colorIndex = data.colorIndex ?? 0;
              const color = ASSIGNED_COLORS[colorIndex];
              return (
                <span
                  key={fdi}
                  className="inline-flex items-center gap-1.5 rounded-full pl-3 pr-1.5 py-1 text-xs"
                  style={{ backgroundColor: `${color}30`, color }}
                >
                  Dent {ours ?? fdi}: {data.name}
                  <button
                    type="button"
                    onClick={() => removeTooth(fdi)}
                    className="rounded-full p-0.5 hover:bg-black/20 transition"
                    title="Retirer"
                    aria-label={`Retirer dent ${ours ?? fdi}`}
                  >
                    <svg className="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12"/></svg>
                  </button>
                </span>
              );
            })}
          </div>
        )}

        {/* Hidden inputs for form submission */}
        {Array.from({ length: 32 }, (_, i) => i + 1).map((ours) => {
          const fdi = OURS_TO_FDI[ours];
          const data = fdi ? teeth[fdi] : null;
          if (!data) return null;
          return (
            <React.Fragment key={ours}>
              <input
                type="hidden"
                name={`teeth[${ours}][stock_id]`}
                value={data.stock_id != null ? data.stock_id : ''}
              />
              <input
                type="hidden"
                name={`teeth[${ours}][prestation_id]`}
                value={data.prestation_id != null ? data.prestation_id : ''}
              />
              <input
                type="hidden"
                name={`teeth[${ours}][quantity]`}
                value="1"
              />
            </React.Fragment>
          );
        })}
      </div>
    </div>
  );
}
