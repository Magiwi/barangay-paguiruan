<style>
    html { scroll-behavior: smooth; }
    .fade-in { opacity: 0; transform: translateY(20px); transition: all 0.6s ease; }
    .fade-in.show { opacity: 1; transform: translateY(0); }
    .fade-delay-1 { transition-delay: 0.1s; }
    .fade-delay-2 { transition-delay: 0.2s; }
    .fade-delay-3 { transition-delay: 0.3s; }
    .fade-delay-4 { transition-delay: 0.4s; }
    .stat-divider { position: relative; }
    .stat-divider::after {
        content: '';
        position: absolute;
        right: 0;
        top: 50%;
        transform: translateY(-50%);
        height: 48px;
        width: 1px;
        background: linear-gradient(to bottom, transparent, #cbd5e1, transparent);
    }
    @media (max-width: 767px) { .stat-divider::after { display: none; } }
    .stat-divider:last-child::after { display: none; }
</style>
