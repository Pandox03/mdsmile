import React, { useEffect, useRef, useState } from 'react';
import { Odontogram } from 'react-odontogram';

// Map MdSmile tooth_number (1-32) to react-odontogram id (teeth-11, teeth-12, ...)
const OURS_TO_ID = {
  1: 'teeth-11', 2: 'teeth-12', 3: 'teeth-13', 4: 'teeth-14', 5: 'teeth-15', 6: 'teeth-16', 7: 'teeth-17', 8: 'teeth-18',
  9: 'teeth-21', 10: 'teeth-22', 11: 'teeth-23', 12: 'teeth-24', 13: 'teeth-25', 14: 'teeth-26', 15: 'teeth-27', 16: 'teeth-28',
  17: 'teeth-31', 18: 'teeth-32', 19: 'teeth-33', 20: 'teeth-34', 21: 'teeth-35', 22: 'teeth-36', 23: 'teeth-37', 24: 'teeth-38',
  25: 'teeth-41', 26: 'teeth-42', 27: 'teeth-43', 28: 'teeth-44', 29: 'teeth-45', 30: 'teeth-46', 31: 'teeth-47', 32: 'teeth-48',
};

const ID_TO_OURS = {
  11: 1, 12: 2, 13: 3, 14: 4, 15: 5, 16: 6, 17: 7, 18: 8,
  21: 9, 22: 10, 23: 11, 24: 12, 25: 13, 26: 14, 27: 15, 28: 16,
  31: 17, 32: 18, 33: 19, 34: 20, 35: 21, 36: 22, 37: 23, 38: 24,
  41: 25, 42: 26, 43: 27, 44: 28, 45: 29, 46: 30, 47: 31, 48: 32,
};

// Display fix requested by clinic: mirror lower-arch labels left/right.
const DISPLAY_FDI = {
  31: 41, 32: 42, 33: 43, 34: 44, 35: 45, 36: 46, 37: 47, 38: 48,
  41: 31, 42: 32, 43: 33, 44: 34, 45: 35, 46: 36, 47: 37, 48: 38,
};

// Same palette as App.jsx - one color per material group
const ASSIGNED_COLORS = [
  '#10b981', // emerald
  '#3b82f6', // blue
  '#8b5cf6', // violet
  '#f59e0b', // amber
  '#ec4899', // pink
  '#06b6d4', // cyan
  '#84cc16', // lime
];

export default function OdontogramShow({ teethData = [] }) {
  const odontogramContainerRef = useRef(null);
  const odontogramRef = useRef(null);
  const [toothPositions, setToothPositions] = useState([]);
  const [svgOverlay, setSvgOverlay] = useState(null); // { left, top, width, height } - overlay to match SVG exactly
  const [selectedPhase, setSelectedPhase] = useState(null); // null = all phases

  useEffect(() => {
    const handler = (e) => setSelectedPhase(e.detail);
    window.addEventListener('mdmile:selectPhase', handler);
    return () => window.removeEventListener('mdmile:selectPhase', handler);
  }, []);

  const displayedTeeth = React.useMemo(
    () =>
      selectedPhase === null
        ? teethData
        : teethData.filter((t) => t.phase === selectedPhase),
    [teethData, selectedPhase]
  );

  // Build: tooth_number -> { colorIndex, stock_name } and assign color per material group
  const { toothToColor, materialLabels } = React.useMemo(() => {
    const toothToColor = {};
    const materialToColorIndex = {};
    const materialLabels = [];
    let nextColorIndex = 0;
    displayedTeeth.forEach(({ tooth_number, stock_id, stock_name }) => {
      const name = stock_name || '—';
      const key = stock_id != null ? `id_${stock_id}` : `name_${name}`;
      if (!(key in materialToColorIndex)) {
        materialToColorIndex[key] = nextColorIndex % ASSIGNED_COLORS.length;
        materialLabels.push({ name, colorIndex: materialToColorIndex[key] });
        nextColorIndex += 1;
      }
      toothToColor[tooth_number] = {
        colorIndex: materialToColorIndex[key],
        stock_name: name,
      };
    });
    return { toothToColor, materialLabels };
  }, [displayedTeeth]);

  // Map tooth_number (1-32) to react-odontogram ids
  const selectedIds = displayedTeeth
    .map((t) => OURS_TO_ID[t.tooth_number])
    .filter(Boolean);

  useEffect(() => {
    const measure = () => {
      const container = odontogramContainerRef.current;
      if (!container) return;
      const svg = container.querySelector('.Odontogram svg');
      if (!svg) return;
      const containerRect = container.getBoundingClientRect();
      const svgRect = svg.getBoundingClientRect();
      const svgW = svgRect.width;
      const svgH = svgRect.height;
      if (!svgW || !svgH) return;
      // Overlay must match SVG position exactly so numbers follow tooth U-shape
      setSvgOverlay({
        left: svgRect.left - containerRect.left,
        top: svgRect.top - containerRect.top,
        width: svgW,
        height: svgH,
      });
      const groups = svg.querySelectorAll('g[class*="teeth-"]');
      const positions = [];
      groups.forEach((g) => {
        const cls = typeof g.className === 'string' ? g.className : (g.className?.baseVal ?? '');
        const match = cls.match(/teeth-(\d+)/);
        if (!match) return;
        const num = parseInt(match[1], 10);
        const ours = ID_TO_OURS[num];
        if (ours == null) return;
        const outlinePath = g.querySelector('path');
        const rect = outlinePath ? outlinePath.getBoundingClientRect() : g.getBoundingClientRect();
        // Positions relative to SVG (not container) - ensures numbers follow tooth U-shape
        const centerX = rect.left - svgRect.left + rect.width / 2;
        const centerY = rect.top - svgRect.top + rect.height / 2;
        const yNudge = rect.height * 0.06;
        const xPercent = (centerX / svgW) * 100;
        const yPercent = (((centerY - yNudge) / svgH) * 100);
        positions.push({
          id: `teeth-${match[1]}`,
          number: DISPLAY_FDI[num] ?? num,
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
    // Re-measure before print to ensure correct layout
    const onBeforePrint = () => setTimeout(measure, 50);
    window.addEventListener('beforeprint', onBeforePrint);
    return () => {
      clearTimeout(t);
      ro?.disconnect();
      window.removeEventListener('beforeprint', onBeforePrint);
    };
  }, [selectedIds]);

  // Apply color per tooth by material group (same as create form)
  useEffect(() => {
    const apply = () => {
      const container = odontogramRef.current;
      if (!container) return;
      const svg = container.querySelector('.Odontogram svg');
      if (!svg) return;
      svg.querySelectorAll('g[class*="teeth-"]').forEach((g) => {
        const cls = typeof g.className === 'string' ? g.className : (g.className?.baseVal ?? '');
        const match = cls.match(/teeth-(\d+)/);
        if (!match) return;
        const ours = ID_TO_OURS[parseInt(match[1], 10)];
        g.classList.remove('assigned', 'assigned-show', 'assigned-0', 'assigned-1', 'assigned-2', 'assigned-3', 'assigned-4', 'assigned-5', 'assigned-6');
        g.style.removeProperty('--assigned-fill');
        const data = toothToColor[ours];
        if (data) {
          const color = ASSIGNED_COLORS[data.colorIndex % ASSIGNED_COLORS.length];
          g.classList.add('assigned', 'assigned-show', `assigned-${data.colorIndex % ASSIGNED_COLORS.length}`);
          g.style.setProperty('--assigned-fill', color);
        }
      });
    };
    const t1 = setTimeout(apply, 150);
    const t2 = setTimeout(apply, 400); // retry in case SVG mounts late
    return () => {
      clearTimeout(t1);
      clearTimeout(t2);
    };
  }, [toothToColor]);

  return (
    <div
      ref={odontogramRef}
      className="odontogram-wrapper rounded-lg border border-zinc-700/50 bg-zinc-950/50 p-6"
      style={{
        '--dark-blue': '#967A4B',
        '--base-blue': '#71717a',
        '--light-blue': '#B8986B',
      }}
    >
      <div ref={odontogramContainerRef} id="odontogram-print-container" className="relative mx-auto max-w-2xl pointer-events-none">
        <Odontogram
          defaultSelected={selectedIds}
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
        {svgOverlay && (
          <div
            className="odontogram-numbers-overlay pointer-events-none absolute"
            aria-hidden
            style={{
              left: `${svgOverlay.left}px`,
              top: `${svgOverlay.top}px`,
              width: `${svgOverlay.width}px`,
              height: `${svgOverlay.height}px`,
            }}
          >
            <div className="absolute inset-0">
              {toothPositions.map(({ number, xPercent, yPercent }) => (
                <span
                  key={number}
                  className="odontogram-tooth-number absolute -translate-x-1/2 -translate-y-1/2 text-xs font-bold text-white drop-shadow-[0_1px_1px_rgba(0,0,0,0.9)] print:drop-shadow-[0_0_1px_black]"
                  style={{ left: `${xPercent}%`, top: `${yPercent}%` }}
                >
                  {number}
                </span>
              ))}
            </div>
          </div>
        )}
      </div>

      {/* Material labels - grouped by material with same colors as odontogram */}
      {materialLabels.length > 0 && (
        <div className="mt-4 flex flex-wrap justify-center gap-2">
          {materialLabels.map(({ name, colorIndex }, i) => {
            const color = ASSIGNED_COLORS[colorIndex % ASSIGNED_COLORS.length];
            return (
              <span
                key={i}
                className="rounded-full px-3 py-1 text-xs"
                style={{ backgroundColor: `${color}30`, color }}
              >
                {name}
              </span>
            );
          })}
        </div>
      )}
    </div>
  );
}
