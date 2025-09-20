(() => { 'use strict';
  const BREAKPOINTS = [400, 500, 600, 700, 800, 900];
  const MIN = bp => `wprm-min-${bp}`;
  const MAX = bp => `wprm-max-${bp}`;

  const observed = new Set();
  const hasRO = 'ResizeObserver' in window;
  const ro = hasRO ? new ResizeObserver(entries => {
    for (const e of entries) apply(e.target);
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