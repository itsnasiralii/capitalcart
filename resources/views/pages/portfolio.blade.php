@php
    $name         = setting('portfolio_name', 'Nasir Ali');
    $title        = setting('portfolio_title', 'Network Engineer & Software Developer');
    $intro        = setting('portfolio_intro', 'I am a full-time Network Engineer at Zong CMPak and a part-time Software Developer with a deep analytical mindset. I specialize in examining large-scale and hyperscale enterprise systems, identifying technical weaknesses, troubleshooting complex issues, and developing practical solutions. I am passionate about innovative ideas, emerging technologies, network automation, and building systems that solve real-world problems. I am flexible, adaptable, and willing to travel whenever professional opportunities require it.');
    $about        = setting('portfolio_about', 'Bridging the critical gap between deep telecommunication infrastructure and modern software engineering. With extensive hands-on enterprise NOC exposure at Zong CMPak and tier-2 TAC experience at Cybernet, I deliver rapid root-cause analysis, streamline packet and routing troubleshooting, and develop resilient automated tooling that prevents operational downtime.');
    $avatar       = setting('portfolio_avatar');
    $email        = setting('portfolio_email', 'nasirali@capitalcart.pk');
    $phone        = setting('portfolio_phone', '03002922584');
    $whatsapp     = setting('portfolio_whatsapp', '03002922584');
    $linkedin     = setting('portfolio_linkedin', 'https://linkedin.com/in/itsnasiralii');
    $github       = setting('portfolio_github', 'https://github.com/itsnasiralii');
    $instagram    = setting('portfolio_instagram', 'https://instagram.com/itsnasiralii');
    $availability = setting('portfolio_availability', 'Available for Network Engineering & Software Solutions');

    $experiences  = \App\Models\PortfolioExperience::active()->ordered()->get();
    $skills       = \App\Models\PortfolioSkill::active()->ordered()->get();
    $projects     = \App\Models\PortfolioProject::active()->ordered()->get();
    $categories   = $projects->pluck('category')->unique()->values();
@endphp

<x-layouts.app :title="$name . ' — ' . $title">

@push('styles')
<style>
    /* Dark Futuristic Aesthetic Variables */
    :root {
        --pf-bg-dark: #070B14;
        --pf-bg-surface: #0D1527;
        --pf-card-bg: rgba(13, 21, 39, 0.7);
        --pf-card-border: rgba(0, 210, 255, 0.15);
        --pf-cyan: #00F2FE;
        --pf-blue: #4FACFE;
        --pf-accent: #00D2FF;
        --pf-orange: #F97316;
        --pf-glow: 0 0 25px rgba(0, 210, 255, 0.25);
    }

    body {
        background-color: var(--pf-bg-dark) !important;
        color: #E2E8F0 !important;
        overflow-x: hidden;
    }

    /* Glassmorphism Classes */
    .glass-card {
        background: var(--pf-card-bg);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border: 1px solid var(--pf-card-border);
        border-radius: 1rem;
        transition: transform 0.3s ease, border-color 0.3s ease, box-shadow 0.3s ease;
    }
    .glass-card:hover {
        border-color: rgba(0, 210, 255, 0.4);
        box-shadow: 0 10px 30px rgba(0, 210, 255, 0.15);
        transform: translateY(-4px);
    }

    /* Buttons */
    .btn-portfolio-primary {
        background: linear-gradient(135deg, #00F2FE 0%, #4FACFE 100%);
        color: #070B14 !important;
        font-weight: 700;
        border: none;
        box-shadow: 0 0 18px rgba(0, 242, 254, 0.35);
        transition: all 0.3s ease;
    }
    .btn-portfolio-primary:hover {
        box-shadow: 0 0 28px rgba(0, 242, 254, 0.6);
        transform: translateY(-2px);
        color: #070B14 !important;
    }

    .btn-portfolio-outline {
        background: rgba(255, 255, 255, 0.04);
        color: #E2E8F0 !important;
        border: 1px solid rgba(255, 255, 255, 0.18);
        font-weight: 600;
        transition: all 0.3s ease;
    }
    .btn-portfolio-outline:hover {
        background: rgba(255, 255, 255, 0.1);
        border-color: var(--pf-cyan);
        color: var(--pf-cyan) !important;
        transform: translateY(-2px);
    }

    /* Inputs */
    .portfolio-input {
        background: rgba(7, 11, 20, 0.8) !important;
        border: 1px solid rgba(255, 255, 255, 0.12) !important;
        color: #F8FAFC !important;
        border-radius: 0.5rem;
        padding: 0.75rem 1rem;
    }
    .portfolio-input:focus {
        border-color: var(--pf-cyan) !important;
        box-shadow: 0 0 12px rgba(0, 242, 254, 0.3) !important;
    }
    .portfolio-input::placeholder {
        color: rgba(255, 255, 255, 0.3) !important;
    }

    /* Timeline styling */
    .timeline-container {
        position: relative;
        padding-left: 2.5rem;
    }
    .timeline-container::before {
        content: '';
        position: absolute;
        left: 11px;
        top: 8px;
        bottom: 8px;
        width: 2px;
        background: linear-gradient(180deg, var(--pf-cyan) 0%, var(--pf-orange) 50%, rgba(0, 210, 255, 0.1) 100%);
    }
    .timeline-item {
        position: relative;
        margin-bottom: 2.5rem;
    }
    .timeline-node {
        position: absolute;
        left: -2.5rem;
        top: 4px;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        background: var(--pf-bg-dark);
        border: 2px solid var(--pf-cyan);
        box-shadow: 0 0 10px var(--pf-cyan);
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .timeline-node::after {
        content: '';
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: var(--pf-cyan);
    }

    /* Text Gradients */
    .text-gradient-cyan {
        background: linear-gradient(135deg, #00F2FE 0%, #4FACFE 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    .text-gradient-orange {
        background: linear-gradient(135deg, #F97316 0%, #FBBF24 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    /* Interactive Canvas Background */
    #network-canvas {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        pointer-events: none;
        z-index: 1;
    }
</style>
@endpush

{{-- JSON-LD Schema for SEO --}}
@push('scripts')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@@type": "Person",
  "name": "{{ $name }}",
  "jobTitle": "{{ $title }}",
  "worksFor": {
    "@type": "Organization",
    "name": "Zong CMPak Ltd"
  },
  "url": "{{ route('portfolio') }}",
  "sameAs": [
    "{{ $linkedin }}",
    "{{ $github }}",
    "{{ $instagram }}"
  ],
  "address": {
    "@type": "PostalAddress",
    "addressLocality": "Islamabad",
    "addressCountry": "PK"
  },
  "description": "{{ $intro }}"
}
</script>
@endpush

{{-- ============================================================================== --}}
{{-- HERO SECTION WITH NETWORK NODE CONSTELLATION --}}
{{-- ============================================================================== --}}
<section class="position-relative py-5 overflow-hidden d-flex align-items-center" style="min-height: 92vh; background: radial-gradient(circle at 50% 20%, rgba(0, 210, 255, 0.08) 0%, rgba(7, 11, 20, 1) 75%);">
    <canvas id="network-canvas"></canvas>

    <div class="container position-relative py-4" style="z-index: 2;">
        <div class="row align-items-center g-5">
            <div class="col-lg-7">
                {{-- Status Badge --}}
                <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill mb-3" style="background: rgba(0, 242, 254, 0.08); border: 1px solid rgba(0, 242, 254, 0.25);">
                    <span style="width: 8px; height: 8px; border-radius: 50%; background: #22C55E; box-shadow: 0 0 8px #22C55E; display: inline-block;"></span>
                    <span class="small fw-semibold text-gradient-cyan">{{ $availability }}</span>
                </div>

                {{-- Name & Identity --}}
                <h1 class="display-3 font-poppins fw-bold text-white mb-2 tracking-tight">
                    {{ $name }}
                </h1>
                <h2 class="h3 fw-semibold mb-3 text-gradient-cyan">
                    {{ $title }}
                </h2>

                {{-- Animated Dynamic Subtitle --}}
                <div class="d-flex align-items-center gap-2 mb-4 text-white-50" style="font-size: 1.1rem; min-height: 32px;">
                    <span style="color: var(--pf-orange); font-family: monospace;">&gt;</span>
                    <span id="animated-typing" class="fw-500 text-white" style="font-family: monospace; border-right: 2px solid var(--pf-cyan); padding-right: 4px;"></span>
                </div>

                {{-- Professional Introduction --}}
                <p class="lead text-light mb-4" style="line-height: 1.7; color: #CBD5E1 !important; font-size: 1.05rem;">
                    {{ $intro }}
                </p>

                {{-- Action Buttons --}}
                <div class="d-flex flex-wrap gap-3 mb-4">
                    <a href="#projects" class="btn btn-portfolio-primary px-4 py-3 d-inline-flex align-items-center gap-2">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0l-7 7m7-7l-7-7"/>
                        </svg>
                        View My Work
                    </a>

                    <a href="{{ route('portfolio.cv.download') }}" class="btn btn-portfolio-outline px-4 py-3 d-inline-flex align-items-center gap-2">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Download CV
                    </a>

                    <a href="#contact" class="btn btn-portfolio-outline px-4 py-3 d-inline-flex align-items-center gap-2">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        Contact Me
                    </a>
                </div>

                {{-- Social Icons --}}
                <div class="d-flex align-items-center gap-3">
                    <span class="small text-white-50 text-uppercase tracking-wider fw-600">Connect:</span>
                    @if($linkedin)
                        <a href="{{ $linkedin }}" target="_blank" class="p-2 rounded-circle glass-card d-inline-flex align-items-center justify-content-center text-white" title="LinkedIn" style="width: 40px; height: 40px;">
                            <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24"><path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/></svg>
                        </a>
                    @endif
                    @if($github)
                        <a href="{{ $github }}" target="_blank" class="p-2 rounded-circle glass-card d-inline-flex align-items-center justify-content-center text-white" title="GitHub" style="width: 40px; height: 40px;">
                            <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/></svg>
                        </a>
                    @endif
                    @if($whatsapp)
                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $whatsapp) }}?text={{ urlencode('Salam Nasir! I saw your portfolio and would like to discuss a project with you.') }}" target="_blank" class="p-2 rounded-circle glass-card d-inline-flex align-items-center justify-content-center text-white" title="WhatsApp" style="width: 40px; height: 40px; color: #25D366 !important;">
                            <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                        </a>
                    @endif
                    @if($email)
                        <a href="mailto:{{ $email }}" class="p-2 rounded-circle glass-card d-inline-flex align-items-center justify-content-center text-white" title="Email" style="width: 40px; height: 40px;">
                            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        </a>
                    @endif
                </div>
            </div>

            {{-- Profile Card / Visual Identity --}}
            <div class="col-lg-5 text-center">
                <div class="position-relative d-inline-block">
                    <div class="p-4 p-md-5 glass-card shadow-lg" style="max-width: 420px; margin: 0 auto; border-color: rgba(0, 242, 254, 0.3);">
                        {{-- Avatar Circle --}}
                        <div class="position-relative mx-auto mb-4" style="width: 140px; height: 140px;">
                            @if($avatar && file_exists(public_path('storage/' . $avatar)))
                                <img src="{{ asset('storage/' . $avatar) }}" alt="{{ $name }}" class="rounded-circle w-100 h-100 object-fit-cover shadow" style="border: 3px solid var(--pf-cyan);">
                            @else
                                <div class="rounded-circle w-100 h-100 d-flex align-items-center justify-content-center shadow" style="background: linear-gradient(135deg, #00D2FF 0%, #3A7BD5 100%); color: #fff; font-size: 3rem; font-weight: 700; border: 3px solid rgba(255,255,255,0.4);">
                                    NA
                                </div>
                            @endif
                            <span class="position-absolute bottom-0 end-0 p-2 rounded-circle" style="background: #22C55E; border: 3px solid var(--pf-bg-dark);" title="Online & Operational"></span>
                        </div>

                        <h3 class="h4 text-white fw-bold mb-1">{{ $name }}</h3>
                        <div class="text-gradient-cyan fw-600 small mb-2">Corporate NOC Engineer</div>
                        <div class="badge px-3 py-1 mb-3" style="background: rgba(249, 115, 22, 0.15); color: #F97316; border: 1px solid rgba(249, 115, 22, 0.4);">
                            Zong CMPak Ltd
                        </div>

                        <p class="text-white-50 small mb-4">
                            Examining large-scale enterprise systems, troubleshooting complex incidents, and developing automated software solutions.
                        </p>

                        {{-- Tech Badges Grid --}}
                        <div class="row g-2 text-start">
                            <div class="col-6">
                                <div class="p-2 rounded" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08);">
                                    <div class="text-white-50" style="font-size:0.7rem">Domain</div>
                                    <div class="text-white fw-bold small">Enterprise NOC</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-2 rounded" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08);">
                                    <div class="text-white-50" style="font-size:0.7rem">Protocols</div>
                                    <div class="text-white fw-bold small">BGP • OSPF • SIP</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-2 rounded" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08);">
                                    <div class="text-white-50" style="font-size:0.7rem">Automation</div>
                                    <div class="text-white fw-bold small">Python • Netmiko</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-2 rounded" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08);">
                                    <div class="text-white-50" style="font-size:0.7rem">Development</div>
                                    <div class="text-white fw-bold small">Laravel 11 • APIs</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ============================================================================== --}}
{{-- ABOUT & PHILOSOPHY SECTION --}}
{{-- ============================================================================== --}}
<section class="py-5 position-relative" style="background: var(--pf-bg-surface); border-top: 1px solid rgba(255,255,255,0.05); border-bottom: 1px solid rgba(255,255,255,0.05);">
    <div class="container py-4">
        <div class="row align-items-center g-5">
            <div class="col-lg-5">
                <span class="badge text-uppercase tracking-wider px-3 py-2 rounded-pill fw-bold mb-3" style="background: rgba(0, 242, 254, 0.1); color: var(--pf-cyan); border: 1px solid rgba(0, 242, 254, 0.3);">
                    Technical Philosophy
                </span>
                <h2 class="display-6 font-poppins fw-bold text-white mb-3">
                    Analyzing Hyperscale Systems, Engineering Resilient Fixes.
                </h2>
                <p class="text-white-50 mb-4" style="line-height: 1.8;">
                    {{ $about }}
                </p>

                <div class="d-flex align-items-center gap-4 text-white-50 small">
                    <div>
                        <div class="fs-3 fw-bold text-white font-poppins">24/7</div>
                        <span>NOC Incident Ready</span>
                    </div>
                    <div style="width: 1px; height: 35px; background: rgba(255,255,255,0.15);"></div>
                    <div>
                        <div class="fs-3 fw-bold text-gradient-cyan font-poppins">Tier-2</div>
                        <span>Escalations & RCA</span>
                    </div>
                    <div style="width: 1px; height: 35px; background: rgba(255,255,255,0.15);"></div>
                    <div>
                        <div class="fs-3 fw-bold text-gradient-orange font-poppins">Global</div>
                        <span>Willing to Travel</span>
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="row g-3">
                    {{-- Highlight 1 --}}
                    <div class="col-md-6">
                        <div class="p-4 glass-card h-100">
                            <div class="d-inline-flex p-3 rounded-3 mb-3" style="background: rgba(0, 242, 254, 0.1); color: var(--pf-cyan);">
                                <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                            </div>
                            <h4 class="h6 fw-bold text-white mb-2">Deep Analytical Mindset</h4>
                            <p class="text-white-50 small mb-0">Systematically analyzing hyperscale network anomalies, packet drops, interface errors, and latency degradation to pinpoint exact root causes.</p>
                        </div>
                    </div>

                    {{-- Highlight 2 --}}
                    <div class="col-md-6">
                        <div class="p-4 glass-card h-100">
                            <div class="d-inline-flex p-3 rounded-3 mb-3" style="background: rgba(249, 115, 22, 0.1); color: var(--pf-orange);">
                                <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                            </div>
                            <h4 class="h6 fw-bold text-white mb-2">Operational Automation</h4>
                            <p class="text-white-50 small mb-0">Writing customized Python, Netmiko, and Scapy automation routines that eliminate manual repetitive tasks and accelerate incident resolution.</p>
                        </div>
                    </div>

                    {{-- Highlight 3 --}}
                    <div class="col-md-6">
                        <div class="p-4 glass-card h-100">
                            <div class="d-inline-flex p-3 rounded-3 mb-3" style="background: rgba(59, 130, 246, 0.1); color: #60A5FA;">
                                <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <h4 class="h6 fw-bold text-white mb-2">Calm Under Pressure</h4>
                            <p class="text-white-50 small mb-0">Thriving during high-severity enterprise network outages, coordinating with Core, RAN, and vendor teams while maintaining strict SLA adherence.</p>
                        </div>
                    </div>

                    {{-- Highlight 4 --}}
                    <div class="col-md-6">
                        <div class="p-4 glass-card h-100">
                            <div class="d-inline-flex p-3 rounded-3 mb-3" style="background: rgba(168, 85, 247, 0.1); color: #C084FC;">
                                <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064"/></svg>
                            </div>
                            <h4 class="h6 fw-bold text-white mb-2">Adaptable & Travel Ready</h4>
                            <p class="text-white-50 small mb-0">Willing to travel and deploy into dynamic on-site engineering environments whenever mission-critical professional opportunities require it.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ============================================================================== --}}
{{-- EXPERIENCE VERTICAL TIMELINE --}}
{{-- ============================================================================== --}}
<section class="py-5" style="background: var(--pf-bg-dark);">
    <div class="container py-4">
        <div class="text-center max-w-2xl mx-auto mb-5">
            <span class="badge text-uppercase tracking-wider px-3 py-2 rounded-pill fw-bold mb-2" style="background: rgba(249, 115, 22, 0.1); color: var(--pf-orange); border: 1px solid rgba(249, 115, 22, 0.3);">
                Career Track
            </span>
            <h2 class="display-6 font-poppins fw-bold text-white">Professional Experience</h2>
            <p class="text-white-50">Proven trajectory in enterprise telecom NOC engineering and software development.</p>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-9">
                <div class="timeline-container">
                    @forelse($experiences as $exp)
                        <div class="timeline-item">
                            <div class="timeline-node"></div>
                            <div class="p-4 p-md-5 glass-card">
                                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                                    <h3 class="h5 fw-bold text-white mb-0">
                                        {{ $exp->role }}
                                    </h3>
                                    <span class="badge px-3 py-1 fw-600 rounded-pill" style="background: rgba(0, 242, 254, 0.1); color: var(--pf-cyan); border: 1px solid rgba(0, 242, 254, 0.3);">
                                        {{ $exp->period }}
                                    </span>
                                </div>

                                <div class="text-gradient-orange fw-bold small mb-3">
                                    {{ $exp->company }}
                                </div>

                                @if($exp->description)
                                    <p class="text-white-50 small mb-3" style="line-height: 1.6;">
                                        {{ $exp->description }}
                                    </p>
                                @endif

                                @if(!empty($exp->responsibilities))
                                    <ul class="list-unstyled d-flex flex-column gap-2 mb-0">
                                        @foreach($exp->responsibilities as $resp)
                                            <li class="d-flex gap-2 text-white-50 small" style="line-height: 1.6;">
                                                <span style="color: var(--pf-cyan); font-weight: bold;">▹</span>
                                                <span>{{ $resp }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4 text-white-50">No experience records configured.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ============================================================================== --}}
{{-- CATEGORIZED SKILLS SECTION --}}
{{-- ============================================================================== --}}
<section class="py-5" style="background: var(--pf-bg-surface); border-top: 1px solid rgba(255,255,255,0.05); border-bottom: 1px solid rgba(255,255,255,0.05);">
    <div class="container py-4">
        <div class="text-center max-w-2xl mx-auto mb-5">
            <span class="badge text-uppercase tracking-wider px-3 py-2 rounded-pill fw-bold mb-2" style="background: rgba(0, 242, 254, 0.1); color: var(--pf-cyan); border: 1px solid rgba(0, 242, 254, 0.3);">
                Core Competencies
            </span>
            <h2 class="display-6 font-poppins fw-bold text-white">Skills & Technologies</h2>
            <p class="text-white-50">Specialized technical proficiencies across networking, telecom, software, and operations.</p>
        </div>

        <div class="row g-4">
            @foreach($skills as $skill)
                <div class="col-md-6 col-lg-4">
                    <div class="p-4 glass-card h-100">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h4 class="h6 fw-bold text-white mb-0 d-flex align-items-center gap-2">
                                <span style="color: var(--pf-cyan)">●</span>
                                {{ $skill->category }}
                            </h4>
                            <span class="badge bg-dark bg-opacity-75 text-white-50 border border-secondary border-opacity-25 small">
                                {{ count($skill->skills ?? []) }} Skills
                            </span>
                        </div>

                        <div class="d-flex flex-wrap gap-2">
                            @foreach($skill->skills ?? [] as $item)
                                <span class="badge px-3 py-2 rounded-pill text-white" style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.12); font-size: 0.8rem; font-weight: 500;">
                                    {{ $item }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ============================================================================== --}}
{{-- FEATURED PROJECTS SECTION (FILTERABLE) --}}
{{-- ============================================================================== --}}
<section id="projects" class="py-5" style="background: var(--pf-bg-dark);">
    <div class="container py-4">
        <div class="text-center max-w-2xl mx-auto mb-4">
            <span class="badge text-uppercase tracking-wider px-3 py-2 rounded-pill fw-bold mb-2" style="background: rgba(249, 115, 22, 0.1); color: var(--pf-orange); border: 1px solid rgba(249, 115, 22, 0.3);">
                Engineering Portfolio
            </span>
            <h2 class="display-6 font-poppins fw-bold text-white">Featured Projects & Tools</h2>
            <p class="text-white-50">Real-world applications, telecom diagnostic tools, and software solutions.</p>
        </div>

        {{-- Filter Pills --}}
        <div class="d-flex justify-content-center flex-wrap gap-2 mb-5">
            <button type="button" class="btn btn-sm btn-portfolio-primary px-3 py-2 rounded-pill project-filter-btn active" data-filter="all">
                All Projects
            </button>
            @foreach($categories as $cat)
                <button type="button" class="btn btn-sm btn-portfolio-outline px-3 py-2 rounded-pill project-filter-btn" data-filter="{{ Str::slug($cat) }}">
                    {{ $cat }}
                </button>
            @endforeach
        </div>

        {{-- Projects Grid --}}
        <div class="row g-4" id="projects-grid">
            @forelse($projects as $proj)
                <div class="col-lg-4 col-md-6 project-card-item" data-category="{{ Str::slug($proj->category) }}">
                    <div class="glass-card h-100 d-flex flex-column overflow-hidden">
                        {{-- Card Header / Category Badge --}}
                        <div class="p-4 pb-0">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge rounded-pill px-3 py-1" style="background: rgba(0, 242, 254, 0.1); color: var(--pf-cyan); border: 1px solid rgba(0, 242, 254, 0.3); font-size: 0.75rem;">
                                    {{ $proj->category }}
                                </span>
                                <span style="width: 8px; height: 8px; border-radius: 50%; background: #22C55E;"></span>
                            </div>
                            <h3 class="h5 fw-bold text-white mb-2">{{ $proj->title }}</h3>
                        </div>

                        {{-- Card Body --}}
                        <div class="p-4 pt-2 flex-grow-1 d-flex flex-column justify-content-between">
                            <p class="text-white-50 small mb-3" style="line-height: 1.6;">
                                {{ $proj->description }}
                            </p>

                            {{-- Tech Stack Tags --}}
                            @if(!empty($proj->tech_stack))
                                <div class="d-flex flex-wrap gap-1 mb-4">
                                    @foreach($proj->tech_stack as $tech)
                                        <span class="badge bg-dark text-white-50 border border-secondary border-opacity-25" style="font-size: 0.7rem;">
                                            {{ $tech }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif

                            {{-- Action Links --}}
                            <div class="d-flex gap-2 pt-2 border-top border-secondary border-opacity-25">
                                @if($proj->demo_url)
                                    <a href="{{ $proj->demo_url }}" target="_blank" class="btn btn-sm btn-portfolio-primary flex-grow-1 d-inline-flex align-items-center justify-content-center gap-1">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                        Live Demo
                                    </a>
                                @endif
                                @if($proj->github_url)
                                    <a href="{{ $proj->github_url }}" target="_blank" class="btn btn-sm btn-portfolio-outline flex-grow-1 d-inline-flex align-items-center justify-content-center gap-1">
                                        <svg width="14" height="14" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/></svg>
                                        GitHub
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center text-white-50 py-5">No projects currently displayed.</div>
            @endforelse
        </div>
    </div>
</section>

{{-- ============================================================================== --}}
{{-- CONTACT SECTION --}}
{{-- ============================================================================== --}}
<section id="contact" class="py-5 position-relative" style="background: var(--pf-bg-surface); border-top: 1px solid rgba(255,255,255,0.05);">
    <div class="container py-4">
        <div class="row g-5 align-items-center">
            <div class="col-lg-5">
                <span class="badge text-uppercase tracking-wider px-3 py-2 rounded-pill fw-bold mb-3" style="background: rgba(0, 242, 254, 0.1); color: var(--pf-cyan); border: 1px solid rgba(0, 242, 254, 0.3);">
                    Initiate Connection
                </span>
                <h2 class="display-6 font-poppins fw-bold text-white mb-3">
                    Let's Build &amp; Troubleshoot Together
                </h2>
                <p class="text-white-50 mb-4" style="line-height: 1.8;">
                    Have an enterprise networking challenge, automation project, or a professional opportunity? Send a direct transmission through the form or reach out directly on WhatsApp or Email.
                </p>

                <div class="d-flex flex-column gap-3 mb-4">
                    <div class="d-flex align-items-center gap-3">
                        <div class="p-2 rounded-circle glass-card text-gradient-cyan d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </div>
                        <div>
                            <div class="text-white-50 small">Location</div>
                            <div class="text-white fw-bold">Islamabad, Pakistan (Willing to Travel)</div>
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-3">
                        <div class="p-2 rounded-circle glass-card text-gradient-cyan d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        </div>
                        <div>
                            <div class="text-white-50 small">Email Address</div>
                            <a href="mailto:{{ $email }}" class="text-white fw-bold text-decoration-none">{{ $email }}</a>
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-3">
                        <div class="p-2 rounded-circle glass-card text-gradient-cyan d-flex align-items-center justify-content-center" style="width: 42px; height: 42px; color: #25D366 !important;">
                            <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                        </div>
                        <div>
                            <div class="text-white-50 small">Direct WhatsApp</div>
                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $whatsapp) }}?text={{ urlencode('Salam Nasir! Let us connect.') }}" target="_blank" class="text-white fw-bold text-decoration-none">+92 {{ substr($whatsapp, 1) }}</a>
                        </div>
                    </div>
                </div>

                {{-- Direct WhatsApp CTA --}}
                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $whatsapp) }}?text={{ urlencode('Salam Nasir! I saw your portfolio and would like to talk with you.') }}" target="_blank" class="btn px-4 py-3 fw-bold text-white d-inline-flex align-items-center gap-2" style="background: #25D366; border: none; border-radius: 0.5rem;">
                    <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                    Direct WhatsApp Message
                </a>
            </div>

            <div class="col-lg-7">
                <div class="p-4 p-md-5 glass-card shadow-lg" style="border-color: rgba(0, 242, 254, 0.25);">
                    <h3 class="h5 fw-bold text-white mb-2">Send a Secure Transmission</h3>
                    <p class="text-white-50 small mb-4">Messages are recorded directly and forwarded with priority.</p>
                    <livewire:portfolio.contact-form />
                </div>
            </div>
        </div>
    </div>
</section>

@push('scripts')
<script>
// ==============================================================================
// 1. Dynamic Text Typing Animation
// ==============================================================================
(function() {
    const phrases = [
        "Corporate NOC Engineer at Zong CMPak",
        "Enterprise Troubleshooting & RCA Specialist",
        "Network Automation with Python & Netmiko",
        "Full-Stack Web & E-Commerce Developer",
        "Hyperscale Packet Diagnostics (Wireshark/Scapy)"
    ];
    let phraseIndex = 0;
    let charIndex = 0;
    let isDeleting = false;
    const typingEl = document.getElementById('animated-typing');

    function type() {
        if (!typingEl) return;
        const currentPhrase = phrases[phraseIndex];

        if (isDeleting) {
            typingEl.textContent = currentPhrase.substring(0, charIndex - 1);
            charIndex--;
        } else {
            typingEl.textContent = currentPhrase.substring(0, charIndex + 1);
            charIndex++;
        }

        let speed = isDeleting ? 40 : 80;

        if (!isDeleting && charIndex === currentPhrase.length) {
            speed = 2000;
            isDeleting = true;
        } else if (isDeleting && charIndex === 0) {
            isDeleting = false;
            phraseIndex = (phraseIndex + 1) % phrases.length;
            speed = 400;
        }

        setTimeout(type, speed);
    }
    type();
})();

// ==============================================================================
// 2. Interactive Network Node Constellation Canvas
// ==============================================================================
(function() {
    const canvas = document.getElementById('network-canvas');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');

    let width, height;
    function resize() {
        width = canvas.width = canvas.parentElement.offsetWidth;
        height = canvas.height = canvas.parentElement.offsetHeight;
    }
    resize();
    window.addEventListener('resize', resize);

    const particles = [];
    const particleCount = Math.min(Math.floor(window.innerWidth / 20), 65);

    class Particle {
        constructor() {
            this.x = Math.random() * width;
            this.y = Math.random() * height;
            this.vx = (Math.random() - 0.5) * 0.7;
            this.vy = (Math.random() - 0.5) * 0.7;
            this.radius = Math.random() * 2 + 1;
        }
        update() {
            this.x += this.vx;
            this.y += this.vy;
            if (this.x < 0 || this.x > width) this.vx *= -1;
            if (this.y < 0 || this.y > height) this.vy *= -1;
        }
        draw() {
            ctx.beginPath();
            ctx.arc(this.x, this.y, this.radius, 0, Math.PI * 2);
            ctx.fillStyle = '#00F2FE';
            ctx.shadowBlur = 8;
            ctx.shadowColor = '#00F2FE';
            ctx.fill();
        }
    }

    for (let i = 0; i < particleCount; i++) {
        particles.push(new Particle());
    }

    function animate() {
        ctx.clearRect(0, 0, width, height);

        for (let i = 0; i < particles.length; i++) {
            particles[i].update();
            particles[i].draw();

            for (let j = i + 1; j < particles.length; j++) {
                const dx = particles[i].x - particles[j].x;
                const dy = particles[i].y - particles[j].y;
                const dist = Math.sqrt(dx * dx + dy * dy);

                if (dist < 120) {
                    ctx.beginPath();
                    ctx.moveTo(particles[i].x, particles[i].y);
                    ctx.lineTo(particles[j].x, particles[j].y);
                    ctx.strokeStyle = `rgba(0, 242, 254, ${1 - dist / 120 * 0.8})`;
                    ctx.lineWidth = 0.6;
                    ctx.shadowBlur = 0;
                    ctx.stroke();
                }
            }
        }
        requestAnimationFrame(animate);
    }
    animate();
})();

// ==============================================================================
// 3. Project Filter Logic
// ==============================================================================
(function() {
    const filterButtons = document.querySelectorAll('.project-filter-btn');
    const projectCards = document.querySelectorAll('.project-card-item');

    filterButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            filterButtons.forEach(b => {
                b.classList.remove('btn-portfolio-primary', 'active');
                b.classList.add('btn-portfolio-outline');
            });
            btn.classList.add('btn-portfolio-primary', 'active');
            btn.classList.remove('btn-portfolio-outline');

            const filter = btn.getAttribute('data-filter');

            projectCards.forEach(card => {
                if (filter === 'all' || card.getAttribute('data-category') === filter) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    });
})();
</script>
@endpush

</x-layouts.app>
