<style>
    .table-skeleton-row td { height: 46px; }
    .table-skeleton-bar { display: block; width: 75%; height: 14px; border-radius: 6px; background: linear-gradient(90deg, #e2e8f0 25%, #f1f5f9 50%, #e2e8f0 75%); background-size: 200% 100%; animation: table-skeleton-shimmer 1.3s ease-in-out infinite; }
    .table-skeleton-row td:nth-child(3n) .table-skeleton-bar { width: 55%; }
    .table-skeleton-row td:nth-child(4n) .table-skeleton-bar { width: 90%; }
    .table-skeleton-sr-only { position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0, 0, 0, 0); white-space: nowrap; border: 0; }
    @keyframes table-skeleton-shimmer { to { background-position-x: -200%; } }
    body.dark-theme .table-skeleton-bar { background-image: linear-gradient(90deg, #334155 25%, #475569 50%, #334155 75%); }
    @media (prefers-reduced-motion: reduce) { .table-skeleton-bar { animation: none; } }
</style>
