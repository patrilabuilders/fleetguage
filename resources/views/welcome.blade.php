<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
        <title>{{ config('app.name', 'Fuel Monitoring') }} - Enterprise Fleet & Fuel Dispensing Management Console</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,900" rel="stylesheet" />
        @vite(['resources/sass/app.scss', 'resources/js/app.js'])
        <style>
            .hero-gradient {
                background: radial-gradient(circle at 10% 20%, rgba(13, 110, 253, 0.15) 0%, transparent 45%),
                            radial-gradient(circle at 90% 80%, rgba(111, 66, 193, 0.12) 0%, transparent 50%);
            }
            .feature-card {
                background-color: #1c1b1f;
                border: 1px solid rgba(255, 255, 255, 0.08);
                border-radius: 12px;
                transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1), border-color 0.25s ease;
            }
            .feature-card:hover {
                transform: translateY(-5px);
                border-color: #0d6efd;
            }
            .pricing-card {
                background-color: #1c1b1f;
                border: 1px solid rgba(255, 255, 255, 0.08);
                border-radius: 16px;
                transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1), border-color 0.25s ease, box-shadow 0.25s ease;
            }
            .pricing-card.premium {
                border-color: rgba(13, 110, 253, 0.5);
                background: linear-gradient(180deg, #1c1b1f 0%, rgba(13, 110, 253, 0.03) 100%);
            }
            .pricing-card:hover {
                transform: translateY(-8px);
                border-color: #0d6efd;
                box-shadow: 0 10px 30px rgba(13, 110, 253, 0.1);
            }
            .nav-blur {
                background-color: rgba(33, 37, 41, 0.85);
                backdrop-filter: blur(10px);
                border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            }
            .interactive-panel {
                background-color: #1c1b1f;
                border: 1px solid rgba(255, 255, 255, 0.08);
                border-radius: 16px;
            }
            .cursor-pointer {
                cursor: pointer;
            }
        </style>
    </head>
    <body class="bg-dark text-light min-vh-100 d-flex flex-column selection-bg-primary">

        <!-- Navigation -->
        <nav class="navbar navbar-expand-lg fixed-top nav-blur navbar-dark py-3">
            <div class="container">
                <a class="navbar-brand d-flex align-items-center gap-2" href="/">
                    <div class="rounded-3 d-flex align-items-center justify-content-center bg-primary" style="width: 38px; height: 38px;">
                        <x-application-logo class="text-white" style="width: 22px; height: 22px;" />
                    </div>
                    <span class="fw-black tracking-tight" style="font-size: 1.25rem;">FleetGuage</span>
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-4">
                        <li class="nav-item">
                            <a class="nav-link cursor-pointer" href="#features">Features</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link cursor-pointer" href="#demo">Interactive Console</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link cursor-pointer" href="#pricing">Plans</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link cursor-pointer" href="#faq">FAQ</a>
                        </li>
                    </ul>
                    <div class="d-flex gap-3 align-items-center">
                        @auth
                            <a href="{{ route('dashboard') }}" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">
                                Go to Console
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="btn btn-outline-light rounded-pill px-4 fw-bold">
                                Sign In
                            </a>
                            <a href="{{ route('register') }}" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">
                                Get Started
                            </a>
                        @endauth
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Hero Section -->
        <header class="hero-gradient pt-5 pb-5 mt-5 d-flex align-items-center flex-grow-1">
            <div class="container py-5 mt-4">
                <div class="row align-items-center g-5">
                    <div class="col-lg-6">
                        <div class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-3 py-2 rounded-pill fw-bold mb-3 small tracking-widest text-uppercase">
                            ⚡ Precise Fleet Fuel Dispensing Console
                        </div>
                        <h1 class="fw-black tracking-tight text-white mb-3" style="font-size: 3.25rem; line-height: 1.1;">
                            Align Your Fuel Dispensation with <span class="text-primary">Verified Usage</span>.
                        </h1>
                        <p class="text-secondary lead mb-4" style="font-size: 1.15rem; font-weight: 400;">
                            Eliminate physical slippage, unauthorized replenishment, and manual entry chaos. Log equipment kilometers and hours, and instantly calculate prescribed fuel orders within defined sub-account budgets.
                        </p>
                        <div class="d-flex flex-wrap gap-3">
                            <a href="{{ route('register') }}" class="btn btn-primary btn-lg rounded-pill px-5 py-3 fw-bold shadow-lg">
                                Start Your Trial
                            </a>
                            <a href="#demo" class="btn btn-outline-secondary btn-lg rounded-pill px-4 py-3 fw-bold">
                                Live Interactive Demo
                            </a>
                        </div>
                        <div class="row mt-5 pt-3 g-4 border-top border-secondary border-opacity-25">
                            <div class="col-4">
                                <h3 class="fw-black text-white mb-1">100%</h3>
                                <p class="small text-secondary mb-0">Audit Compliance</p>
                            </div>
                            <div class="col-4">
                                <h3 class="fw-black text-white mb-1">&lt;15m</h3>
                                <p class="small text-secondary mb-0">Initial Setup</p>
                            </div>
                            <div class="col-4">
                                <h3 class="fw-black text-white mb-1">0%</h3>
                                <p class="small text-secondary mb-0">Manual Error Rate</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <!-- Simulated Console Dashboard View -->
                        <div class="interactive-panel p-4 shadow-2xl border border-secondary border-opacity-25">
                            <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom border-secondary border-opacity-25">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="bg-success rounded-circle" style="width: 10px; height: 10px;"></div>
                                    <span class="small fw-bold text-secondary text-uppercase tracking-widest">Active Dispatcher console</span>
                                </div>
                                <div class="d-flex gap-1">
                                    <div class="bg-secondary rounded-circle" style="width: 8px; height: 8px; opacity: 0.3;"></div>
                                    <div class="bg-secondary rounded-circle" style="width: 8px; height: 8px; opacity: 0.3;"></div>
                                    <div class="bg-secondary rounded-circle" style="width: 8px; height: 8px; opacity: 0.3;"></div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="card bg-dark border-secondary border-opacity-25 p-3 rounded-3">
                                            <div class="text-secondary small text-uppercase tracking-wider fw-bold mb-1">Prescribed Order</div>
                                            <h3 class="fw-black text-primary mb-0" id="hero-order-amt">180.0 L</h3>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card bg-dark border-secondary border-opacity-25 p-3 rounded-3">
                                            <div class="text-secondary small text-uppercase tracking-wider fw-bold mb-1">Assumed Fuel Factor</div>
                                            <h3 class="fw-black text-light mb-0" id="hero-factor-amt">0.45 L/Km</h3>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="vstack gap-3">
                                <div>
                                    <label class="form-label text-secondary small fw-bold text-uppercase tracking-widest ms-1">Selected Equipment Type</label>
                                    <select class="form-select bg-dark border-secondary border-opacity-50" id="hero-asset-type" style="padding: 0.65rem 1rem;">
                                        <option value="truck" data-factor="0.45" data-unit="Km" data-tank="300">Highway Transport Truck (L/Km)</option>
                                        <option value="excavator" data-factor="12.5" data-unit="Hour" data-tank="450">Stationary Hydraulic Excavator (L/Hour)</option>
                                        <option value="suv" data-factor="0.12" data-unit="Km" data-tank="80">Operations Support SUV (L/Km)</option>
                                    </select>
                                </div>

                                <div class="row g-3">
                                    <div class="col-6">
                                        <label class="form-label text-secondary small fw-bold text-uppercase tracking-widest ms-1" id="hero-reading-label">Last Readings (Km)</label>
                                        <input type="number" class="form-control bg-dark border-secondary border-opacity-50" id="hero-last-reading" value="24000" style="padding: 0.65rem 1rem;">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label text-secondary small fw-bold text-uppercase tracking-widest ms-1" id="hero-current-reading-label">Current Readings (Km)</label>
                                        <input type="number" class="form-control bg-dark border-secondary border-opacity-50" id="hero-current-reading" value="24400" style="padding: 0.65rem 1rem;">
                                    </div>
                                </div>

                                <div class="bg-dark bg-opacity-50 rounded-3 p-3 border border-secondary border-opacity-25 mt-1">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="small fw-bold text-secondary">Tank Capacity:</span>
                                        <span class="small fw-black text-light" id="hero-tank-capacity">300 L</span>
                                    </div>
                                    <div class="progress bg-secondary bg-opacity-25" style="height: 6px;">
                                        <div class="progress-bar bg-primary progress-bar-striped progress-bar-animated" id="hero-progress-bar" role="progressbar" style="width: 60%"></div>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mt-2">
                                        <span class="small text-secondary" id="hero-calc-desc">Calculated replacement fuel based on 400 Km run</span>
                                        <span class="small fw-bold text-success" id="hero-limit-check">Under Limit</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Features Grid Section -->
        <section id="features" class="py-5 bg-dark bg-opacity-50 border-top border-bottom border-secondary border-opacity-25">
            <div class="container py-5">
                <div class="text-center mb-5 max-w-2xl mx-auto">
                    <h6 class="text-uppercase tracking-widest text-primary fw-black mb-2" style="font-size: 0.85rem;">System Core Features</h6>
                    <h2 class="fw-black text-white" style="font-size: 2.25rem;">Built to Secure and Audit Every Drop</h2>
                    <p class="text-secondary" style="max-width: 600px; margin: 0 auto;">Everything you need to run an accountable physical fuel station, seamlessly connected to corporate budget lines.</p>
                </div>

                <div class="row g-4 mt-2">
                    <div class="col-md-4">
                        <div class="feature-card p-4 h-100">
                            <div class="rounded-3 bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center mb-4" style="width: 50px; height: 50px;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-activity"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline></svg>
                            </div>
                            <h5 class="fw-bold text-white mb-2">Dual Asset Loggers</h5>
                            <p class="text-secondary small mb-0">Support both Kilometer-based (transportation trucks, operations SUVs) and Hour-based (stationary generators, hydraulic excavators) asset utilization logging.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="feature-card p-4 h-100">
                            <div class="rounded-3 bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center mb-4" style="width: 50px; height: 50px;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-cpu"><rect x="4" y="4" width="16" height="16" rx="2" ry="2"></rect><rect x="9" y="9" width="6" height="6"></rect><line x1="9" y1="1" x2="9" y2="4"></line><line x1="15" y1="1" x2="15" y2="4"></line><line x1="9" y1="20" x2="9" y2="23"></line><line x1="15" y1="20" x2="15" y2="23"></line><line x1="20" y1="9" x2="23" y2="9"></line><line x1="20" y1="15" x2="23" y2="15"></line><line x1="1" y1="9" x2="4" y2="9"></line><line x1="1" y1="15" x2="4" y2="15"></line></svg>
                            </div>
                            <h5 class="fw-bold text-white mb-2">Automated Estimation</h5>
                            <p class="text-secondary small mb-0">Our proprietary engine calculates estimated fuel replenishment dynamically based on previous logs, preventing human math errors and artificial inflate tricks.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="feature-card p-4 h-100">
                            <div class="rounded-3 bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center mb-4" style="width: 50px; height: 50px;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-dollar-sign"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                            </div>
                            <h5 class="fw-bold text-white mb-2">Sub-Account Budgeting</h5>
                            <p class="text-secondary small mb-0">Segment your primary company finances into chargeable accounts and sub-accounts. Allocate budgets, apply approval locks, and enforce spending rules.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="feature-card p-4 h-100">
                            <div class="rounded-3 bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center mb-4" style="width: 50px; height: 50px;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-file-text"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                            </div>
                            <h5 class="fw-bold text-white mb-2">Digital Fuel Orders</h5>
                            <p class="text-secondary small mb-0">Generate print-ready digital fuel orders. Let fuel-men record actual fuel filled (actualization) to capture precise variances in physical tanks.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="feature-card p-4 h-100">
                            <div class="rounded-3 bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center mb-4" style="width: 50px; height: 50px;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-pie-chart"><path d="M21.21 15.89A10 10 0 1 1 8 2.83"></path><path d="M22 12A10 10 0 0 0 12 2v10z"></path></svg>
                            </div>
                            <h5 class="fw-bold text-white mb-2">Analytical Insights</h5>
                            <p class="text-secondary small mb-0">Export precise utilization charts, asset metrics, and chargeable account breakdowns to meet corporate accounting or green carbon compliance standards.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="feature-card p-4 h-100">
                            <div class="rounded-3 bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center mb-4" style="width: 50px; height: 50px;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-shield"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                            </div>
                            <h5 class="fw-bold text-white mb-2">SaaS Tenancy Security</h5>
                            <p class="text-secondary small mb-0">Completely isolated multi-tenant architecture ensures your organization's assets, logs, users, and financial transactions are securely isolated and encrypted.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Live Demo / Interactive Calculator -->
        <section id="demo" class="py-5 bg-dark">
            <div class="container py-5">
                <div class="row align-items-center g-5">
                    <div class="col-lg-6">
                        <h6 class="text-uppercase tracking-widest text-primary fw-black mb-2" style="font-size: 0.85rem;">Live Interactive Console</h6>
                        <h2 class="fw-black text-white" style="font-size: 2.25rem;">Try the Replacement Estimator</h2>
                        <p class="text-secondary mb-4">See how the system enforces safety constraints in real-time. Toggle different machinery below, insert mock odometer or hour-meter readings, and watch the calculated fuel dispenser slip render instantly.</p>
                        
                        <div class="vstack gap-3 border-start border-primary border-opacity-50 ps-4 py-2">
                            <div class="step-desc">
                                <span class="badge bg-primary rounded-circle mb-2" style="width: 20px; height: 20px; padding: 0; line-height: 20px;">1</span>
                                <h6 class="fw-bold text-light mb-1">Interactive Estimation</h6>
                                <p class="text-secondary small mb-0">Odometer jumps trigger automated fuel limits so physical dispensations exactly match mechanical usage.</p>
                            </div>
                            <div class="step-desc">
                                <span class="badge bg-primary rounded-circle mb-2" style="width: 20px; height: 20px; padding: 0; line-height: 20px;">2</span>
                                <h6 class="fw-bold text-light mb-1">Safety Capacity Lock</h6>
                                <p class="text-secondary small mb-0">Inputting reading differences that exceed physical tank capacities will trigger automated warnings.</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="interactive-panel p-4 border border-secondary border-opacity-25 shadow-2xl">
                            <div class="d-flex justify-content-between align-items-center mb-4 border-bottom border-secondary border-opacity-25 pb-2">
                                <h5 class="fw-black text-white mb-0">Interactive Dispatch Console</h5>
                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">Ready</span>
                            </div>

                            <div class="mb-3">
                                <label class="form-label text-secondary small fw-bold text-uppercase tracking-widest">Select Asset Profile</label>
                                <div class="row g-2">
                                    <div class="col-4">
                                        <button class="btn btn-outline-primary w-100 py-3 active demo-btn" data-asset="excavator" data-factor="15" data-unit="Hour" data-tank="350">
                                            🚧 Excavator<br><small class="text-secondary">15L / Hr</small>
                                        </button>
                                    </div>
                                    <div class="col-4">
                                        <button class="btn btn-outline-primary w-100 py-3 demo-btn" data-asset="truck" data-factor="0.4" data-unit="Km" data-tank="400">
                                            🚛 Heavy Truck<br><small class="text-secondary">0.4L / Km</small>
                                        </button>
                                    </div>
                                    <div class="col-4">
                                        <button class="btn btn-outline-primary w-100 py-3 demo-btn" data-asset="generator" data-factor="5" data-unit="Hour" data-tank="100">
                                            ⚙️ Generator<br><small class="text-secondary">5L / Hr</small>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-6">
                                    <label class="form-label text-secondary small fw-bold text-uppercase tracking-widest" id="demo-last-lbl">Last Hour Meter</label>
                                    <input type="number" class="form-control bg-dark border-secondary border-opacity-50" id="demo-last-reading" value="1250">
                                </div>
                                <div class="col-6">
                                    <label class="form-label text-secondary small fw-bold text-uppercase tracking-widest" id="demo-curr-lbl">Current Hour Meter</label>
                                    <input type="number" class="form-control bg-dark border-secondary border-opacity-50" id="demo-current-reading" value="1270">
                                </div>
                            </div>

                            <div class="card bg-dark border-secondary border-opacity-25 p-4 rounded-4 text-center mb-3">
                                <div class="text-secondary small text-uppercase tracking-widest fw-bold mb-1">Prescribed Dispensing Order</div>
                                <h1 class="fw-black text-primary display-4 mb-2" id="demo-calc-result">300.0 L</h1>
                                <p class="text-secondary small mb-0" id="demo-calc-breakdown">Calculated replacement fuel based on 20.0 Hours run time</p>
                            </div>

                            <div class="alert alert-danger d-none" id="demo-tank-error" role="alert">
                                ⚠️ <strong>Limit Exceeded:</strong> Computed fuel requirement of <span id="error-amt">300L</span> exceeds asset's mechanical tank capacity (<span id="error-tank">350L</span>).
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Pricing Plans Section -->
        <section id="pricing" class="py-5 bg-dark bg-opacity-50 border-top border-bottom border-secondary border-opacity-25">
            <div class="container py-5">
                <div class="text-center mb-5 max-w-2xl mx-auto">
                    <h6 class="text-uppercase tracking-widest text-primary fw-black mb-2" style="font-size: 0.85rem;">Transparent Subscription plans</h6>
                    <h2 class="fw-black text-white" style="font-size: 2.25rem;">Scale From Local Sites to Full Fleets</h2>
                    <p class="text-secondary" style="max-width: 600px; margin: 0 auto;">Select the standard tier to get started, or scale to enterprise capacity with analytical reports enabled.</p>
                </div>

                <div class="row g-4 justify-content-center">
                    <div class="col-lg-5 col-md-6">
                        <div class="pricing-card p-4 p-lg-5 h-100">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h4 class="fw-black text-white mb-0">Standard Plan</h4>
                                <span class="badge bg-secondary bg-opacity-25 text-light px-3 py-2 rounded-pill small">SME Essential</span>
                            </div>
                            <p class="text-secondary small mb-4">Perfect for regional construction sites or single-depot logistics hubs.</p>
                            <h2 class="fw-black text-white mb-4">$49<span class="fs-5 text-secondary fw-normal">/month</span></h2>
                            
                            <ul class="list-unstyled mb-5 vstack gap-3 text-secondary" style="font-size: 0.9rem;">
                                <li class="d-flex align-items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#28a745" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check-circle"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                                    <span>Up to <strong>5 Active Users</strong></span>
                                </li>
                                <li class="d-flex align-items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#28a745" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check-circle"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                                    <span>Up to <strong>10 Monitored Assets</strong></span>
                                </li>
                                <li class="d-flex align-items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#28a745" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check-circle"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                                    <span>Dual KM and Hour loggers</span>
                                </li>
                                <li class="d-flex align-items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#28a745" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check-circle"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                                    <span>Digital Fuel Order PDF prints</span>
                                </li>
                                <li class="text-decoration-line-through text-opacity-50 d-flex align-items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#dc3545" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x-circle"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
                                    <span>Analytical reporting modules</span>
                                </li>
                            </ul>

                            <a href="{{ route('register') }}?tier_id=1" class="btn btn-outline-light w-100 rounded-pill py-3 fw-bold">
                                Select Standard Plan
                            </a>
                        </div>
                    </div>

                    <div class="col-lg-5 col-md-6">
                        <div class="pricing-card premium p-4 p-lg-5 h-100 border-primary">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h4 class="fw-black text-white mb-0">Enterprise Plan</h4>
                                <span class="badge bg-primary text-white px-3 py-2 rounded-pill small">Popular</span>
                            </div>
                            <p class="text-secondary small mb-4">Complete solution for industrial operations, mining networks, and national shipping fleets.</p>
                            <h2 class="fw-black text-white mb-4">$299<span class="fs-5 text-secondary fw-normal">/month</span></h2>
                            
                            <ul class="list-unstyled mb-5 vstack gap-3 text-secondary" style="font-size: 0.9rem;">
                                <li class="d-flex align-items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#28a745" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check-circle"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                                    <span>Up to <strong>100 Active Users</strong></span>
                                </li>
                                <li class="d-flex align-items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#28a745" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check-circle"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                                    <span>Up to <strong>1,000 Monitored Assets</strong></span>
                                </li>
                                <li class="d-flex align-items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#28a745" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check-circle"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                                    <span>Dual KM and Hour loggers</span>
                                </li>
                                <li class="d-flex align-items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#28a745" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check-circle"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                                    <span>Digital Fuel Order PDF prints</span>
                                </li>
                                <li class="d-flex align-items-center gap-2 text-white fw-bold">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#0d6efd" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check-circle"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                                    <span>Full Analytical Reports Enabled</span>
                                </li>
                            </ul>

                            <a href="{{ route('register') }}?tier_id=2" class="btn btn-primary w-100 rounded-pill py-3 fw-bold">
                                Select Enterprise Plan
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- FAQ Accordion Section -->
        <section id="faq" class="py-5 bg-dark">
            <div class="container py-5">
                <div class="text-center mb-5 max-w-2xl mx-auto">
                    <h6 class="text-uppercase tracking-widest text-primary fw-black mb-2" style="font-size: 0.85rem;">System FAQ</h6>
                    <h2 class="fw-black text-white" style="font-size: 2.25rem;">Frequently Asked Questions</h2>
                    <p class="text-secondary" style="max-width: 600px; margin: 0 auto;">Everything you need to know about setting up your fuel dispensation parameters.</p>
                </div>

                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <div class="accordion accordion-flush" id="faqAccordion">
                            <div class="accordion-item bg-transparent border-bottom border-secondary border-opacity-25 py-3">
                                <h2 class="accordion-header" id="headingOne">
                                    <button class="accordion-button collapsed bg-transparent text-white fw-bold border-0 shadow-none px-0" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                                        How does the automated fuel estimation calculation work?
                                    </button>
                                </h2>
                                <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body px-0 text-secondary small">
                                        The system compares an asset's previous kilometer/hour log against its current log to get the actual run usage. It then multiplies that physical usage by the asset's pre-defined consumption factor to output the exact volume of fuel needed to fill the tank back up.
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item bg-transparent border-bottom border-secondary border-opacity-25 py-3">
                                <h2 class="accordion-header" id="headingTwo">
                                    <button class="accordion-button collapsed bg-transparent text-white fw-bold border-0 shadow-none px-0" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                        Can I bypass/override the calculated fuel limit if an asset runs out on site?
                                    </button>
                                </h2>
                                <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body px-0 text-secondary small">
                                        Yes. If physical conditions dictate a variance (e.g., fuel leak or off-grid usage), Administrators and Moderators can apply a system override or "Waiver" directly inside the Fuel Order generation form to authorize dispensations exceeding calculated limits.
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item bg-transparent border-bottom border-secondary border-opacity-25 py-3">
                                <h2 class="accordion-header" id="headingThree">
                                    <button class="accordion-button collapsed bg-transparent text-white fw-bold border-0 shadow-none px-0" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                        Is my company's data shared with other tenants?
                                    </button>
                                </h2>
                                <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body px-0 text-secondary small">
                                        Absolutely not. Our database uses strict scope filtering (`TenantScope`) that isolates all database rows based on your registered `company_id`. No other user outside your registered organization can query or access your assets, budgets, or slips.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-dark nav-blur py-5 mt-auto">
            <div class="container text-center">
                <div class="d-flex justify-content-center align-items-center gap-2 mb-4">
                    <div class="rounded-3 d-flex align-items-center justify-content-center bg-primary" style="width: 30px; height: 30px;">
                        <x-application-logo class="text-white" style="width: 18px; height: 18px;" />
                    </div>
                    <span class="fw-black text-white tracking-tight" style="font-size: 1rem;">FleetGuage Console</span>
                </div>
                <p class="text-secondary small mb-3">Enterprise-grade machinery fuel replenishment auditing and multi-tenant telemetry platform.</p>
                <p class="small text-secondary mb-0" style="font-size: 0.75rem;">
                    &copy; {{ date('Y') }} FleetGuage Inc. All rights reserved. Built for high-density outdoor depot environments.
                </p>
            </div>
        </footer>

        <!-- Interactive Calculator Logic -->
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Interactive hero card selectors
                const heroAssetType = document.getElementById('hero-asset-type');
                const heroLastReading = document.getElementById('hero-last-reading');
                const heroCurrentReading = document.getElementById('hero-current-reading');
                const heroOrderAmt = document.getElementById('hero-order-amt');
                const heroFactorAmt = document.getElementById('hero-factor-amt');
                const heroReadingLabel = document.getElementById('hero-reading-label');
                const heroCurrentReadingLabel = document.getElementById('hero-current-reading-label');
                const heroTankCapacity = document.getElementById('hero-tank-capacity');
                const heroProgressBar = document.getElementById('hero-progress-bar');
                const heroCalcDesc = document.getElementById('hero-calc-desc');
                const heroLimitCheck = document.getElementById('hero-limit-check');

                function updateHeroCalculator() {
                    const selectedOpt = heroAssetType.options[heroAssetType.selectedIndex];
                    const factor = parseFloat(selectedOpt.getAttribute('data-factor'));
                    const unit = selectedOpt.getAttribute('data-unit');
                    const tank = parseFloat(selectedOpt.getAttribute('data-tank'));

                    heroReadingLabel.textContent = `Last Readings (${unit})`;
                    heroCurrentReadingLabel.textContent = `Current Readings (${unit})`;
                    heroFactorAmt.textContent = `${factor} L/${unit}`;
                    heroTankCapacity.textContent = `${tank} L`;

                    const lastVal = parseFloat(heroLastReading.value) || 0;
                    const currVal = parseFloat(heroCurrentReading.value) || 0;
                    const runValue = Math.max(0, currVal - lastVal);

                    const orderLitres = runValue * factor;
                    heroOrderAmt.textContent = `${orderLitres.toFixed(1)} L`;
                    heroCalcDesc.textContent = `Calculated replacement fuel based on ${runValue} ${unit} run`;

                    const fillPercentage = Math.min(100, (orderLitres / tank) * 100);
                    heroProgressBar.style.width = `${fillPercentage}%`;

                    if (orderLitres > tank) {
                        heroProgressBar.classList.remove('bg-primary');
                        heroProgressBar.classList.add('bg-danger');
                        heroLimitCheck.textContent = 'Limit Exceeded';
                        heroLimitCheck.classList.remove('text-success');
                        heroLimitCheck.classList.add('text-danger');
                    } else {
                        heroProgressBar.classList.remove('bg-danger');
                        heroProgressBar.classList.add('bg-primary');
                        heroLimitCheck.textContent = 'Under Limit';
                        heroLimitCheck.classList.remove('text-danger');
                        heroLimitCheck.classList.add('text-success');
                    }
                }

                if (heroAssetType) {
                    heroAssetType.addEventListener('change', updateHeroCalculator);
                    heroLastReading.addEventListener('input', updateHeroCalculator);
                    heroCurrentReading.addEventListener('input', updateHeroCalculator);
                    updateHeroCalculator();
                }

                // Interactive Demo Card Selection
                const demoButtons = document.querySelectorAll('.demo-btn');
                const demoLastReading = document.getElementById('demo-last-reading');
                const demoCurrentReading = document.getElementById('demo-current-reading');
                const demoCalcResult = document.getElementById('demo-calc-result');
                const demoCalcBreakdown = document.getElementById('demo-calc-breakdown');
                const demoLastLbl = document.getElementById('demo-last-lbl');
                const demoCurrLbl = document.getElementById('demo-curr-lbl');
                const demoTankError = document.getElementById('demo-tank-error');
                const errorAmt = document.getElementById('error-amt');
                const errorTank = document.getElementById('error-tank');

                let currentFactor = 15;
                let currentUnit = 'Hour';
                let currentTank = 350;

                function updateDemoCalculator() {
                    demoLastLbl.textContent = `Last Meter (${currentUnit})`;
                    demoCurrLbl.textContent = `Current Meter (${currentUnit})`;

                    const lastVal = parseFloat(demoLastReading.value) || 0;
                    const currVal = parseFloat(demoCurrentReading.value) || 0;
                    const diff = Math.max(0, currVal - lastVal);

                    const calculatedFuel = diff * currentFactor;
                    demoCalcResult.textContent = `${calculatedFuel.toFixed(1)} L`;
                    demoCalcBreakdown.textContent = `Calculated replacement fuel based on ${diff.toFixed(1)} ${currentUnit}s run time`;

                    if (calculatedFuel > currentTank) {
                        demoTankError.classList.remove('d-none');
                        errorAmt.textContent = `${calculatedFuel.toFixed(1)}L`;
                        errorTank.textContent = `${currentTank}L`;
                        demoCalcResult.classList.remove('text-primary');
                        demoCalcResult.classList.add('text-danger');
                    } else {
                        demoTankError.classList.add('d-none');
                        demoCalcResult.classList.remove('text-danger');
                        demoCalcResult.classList.add('text-primary');
                    }
                }

                demoButtons.forEach(btn => {
                    btn.addEventListener('click', function() {
                        demoButtons.forEach(b => b.classList.remove('active'));
                        this.classList.add('active');

                        currentFactor = parseFloat(this.getAttribute('data-factor'));
                        currentUnit = this.getAttribute('data-unit');
                        currentTank = parseFloat(this.getAttribute('data-tank'));

                        if (currentUnit === 'Hour') {
                            demoLastReading.value = 1250;
                            demoCurrentReading.value = 1270;
                        } else {
                            demoLastReading.value = 45000;
                            demoCurrentReading.value = 45400;
                        }

                        updateDemoCalculator();
                    });
                });

                if (demoLastReading) {
                    demoLastReading.addEventListener('input', updateDemoCalculator);
                    demoCurrentReading.addEventListener('input', updateDemoCalculator);
                    updateDemoCalculator();
                }
            });
        </script>
    </body>
</html>
