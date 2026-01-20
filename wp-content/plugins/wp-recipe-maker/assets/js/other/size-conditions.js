(() => { 'use strict';
  const BREAKPOINTS = [400, 500, 600, 700, 800, 900];
  const MIN = bp => `wprm-min-${bp}`;
  const MAX = bp => `wprm-max-${bp}`;

  const observed = new Set();
  const hasRO = 'ResizeObserver' in window;
  
  // Track pending updates to prevent loops
  const pendingUpdates = new WeakMap();
  // Track last known width to avoid unnecessary updates
  const lastWidths = new WeakMap();
  
  const ro = hasRO ? new ResizeObserver(entries => {
    // Use requestAnimationFrame to batch updates and prevent loops
    requestAnimationFrame(() => {
      for (const e of entries) {
        // Skip if this element already has a pending update
        if (pendingUpdates.has(e.target)) {
          continue;
        }
        
        // Check if width actually changed to avoid unnecessary DOM modifications
        const currentWidth = e.target.getBoundingClientRect().width;
        const lastWidth = lastWidths.get(e.target);
        
        // Only process if width changed significantly (more than EPS)
        if (lastWidth !== undefined && Math.abs(currentWidth - lastWidth) < 0.1) {
          continue;
        }
        
        // Mark as pending and store width
        pendingUpdates.set(e.target, true);
        lastWidths.set(e.target, currentWidth);
        
        try {
          apply(e.target);
        } catch (error) {
          // Log unexpected errors (ResizeObserver loop errors are browser warnings, not thrown)
          console.warn('WPRM size-conditions error:', error);
        } finally {
          // Clear pending flag after a frame
          requestAnimationFrame(() => {
            pendingUpdates.delete(e.target);
          });
        }
      }
    });
  }) : null;

  // Use border-box width (includes borders), with a DP-aware epsilon to avoid toggle jitter.
  const EPS = 0.5 / (window.devicePixelRatio || 1); // ~0.5 CSS px at DPR=1, smaller at higher DPR
  function getBoxWidth(el){
    // getBoundingClientRect().width is fractional border-box width
    return el.getBoundingClientRect().width || 0;
  }

  function apply(container){
    const w = getBoxWidth(container);
    for (let i = 0; i < BREAKPOINTS.length; i++){
      const bp = BREAKPOINTS[i];
      const isMax = w <= bp + EPS;  // include equality, tolerant to tiny float drift
      const isMin = w >  bp + EPS;  // strictly greater
      if (isMin) container.classList.add(MIN(bp)); else container.classList.remove(MIN(bp));
      if (isMax) container.classList.add(MAX(bp)); else container.classList.remove(MAX(bp));
    }
  }

  function observe(container){
    if (observed.has(container)) return;
    observed.add(container);
    ro && ro.observe(container);
    apply(container);
  }

  function scan(root = document){ root.querySelectorAll('.wprm-recipe').forEach(observe); }
  scan();

  const mo = new MutationObserver(muts => {
    for (const m of muts){
      for (const n of m.addedNodes){
        if (!(n instanceof Element)) continue;
        if (n.matches?.('.wprm-recipe')) observe(n);
        n.querySelectorAll?.('.wprm-recipe').forEach(observe);
      }
    }
  });
  mo.observe(document.documentElement, { childList: true, subtree: true });

  if (!hasRO){
    let raf = 0;
    addEventListener('resize', () => {
      if (raf) cancelAnimationFrame(raf);
      raf = requestAnimationFrame(() => observed.forEach(apply));
    }, { passive: true });
  }
})();