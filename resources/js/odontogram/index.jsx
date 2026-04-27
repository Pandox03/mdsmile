import React from 'react';
import { createRoot } from 'react-dom/client';
import OdontogramApp from './App';

const rootEl = document.getElementById('odontogram-root');
if (rootEl) {
  const stockItems = typeof window.MDSMILE_STOCK_ITEMS !== 'undefined'
    ? window.MDSMILE_STOCK_ITEMS
    : [];
  const initialTeeth = typeof window.MDSMILE_INITIAL_TEETH !== 'undefined'
    ? window.MDSMILE_INITIAL_TEETH
    : [];
  const prestations = typeof window.MDSMILE_PRESTATIONS !== 'undefined'
    ? window.MDSMILE_PRESTATIONS
    : [];
  createRoot(rootEl).render(
    <OdontogramApp
      stockItems={stockItems}
      initialTeeth={initialTeeth}
      prestations={prestations}
    />
  );
}
