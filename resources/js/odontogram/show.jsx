import React from 'react';
import { createRoot } from 'react-dom/client';
import OdontogramShow from './OdontogramShow';

const rootEl = document.getElementById('odontogram-show-root');
if (rootEl) {
  const teethData = typeof window.MDSMILE_TEETH_DATA !== 'undefined'
    ? window.MDSMILE_TEETH_DATA
    : [];
  createRoot(rootEl).render(<OdontogramShow teethData={teethData} />);
}
