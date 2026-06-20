<?php
$activePage = 'home';
require_once __DIR__ . '/handlers/vehicle-request.php';
fleetAuthStartSession();
extract(vehicleRequestFetchPublicPageData());
include __DIR__ . '/includes/header.php';

$homeUrl = ($basePath ?: '') . '/home';
$loginUrl = ($basePath ?: '') . '/login';
$dashboardUrl = ($basePath ?: '') . '/dashboard';
$heroImageUrl = ($basePath ?: '') . '/assets/images/hero/heroimage.png';
$brandingLogoImage = ($basePath ?: '') . '/assets/images/branding/logo1.png';
$homeUserRole = trim((string) ($_SESSION['user_role'] ?? ''));
$homeUserIsLoggedIn = $homeUserRole !== '';
$homeCanViewRequestForm = $homeUserRole !== 'admin';
?>
<main class="landing-page min-h-screen overflow-hidden bg-fleet-canvas">
    <header data-home-header class="absolute inset-x-0 top-0 z-50 border-b border-fleet-line bg-white/95 backdrop-blur transition duration-200">
        <div class="mx-auto flex h-20 max-w-7xl items-center justify-between px-5 sm:px-6 lg:px-8">
            <a href="<?= htmlspecialchars($homeUrl, ENT_QUOTES, 'UTF-8'); ?>" class="flex items-center gap-4">
                <span class="flex h-12 w-12 items-center justify-center overflow-hidden rounded-[1rem] bg-white p-1 shadow-sm">
                    <img src="<?= htmlspecialchars($brandingLogoImage, ENT_QUOTES, 'UTF-8'); ?>" alt="BUESMIS logo" class="h-full w-full object-contain">
                </span>
                <span>
                    <span class="block text-xl font-black tracking-tight text-fleet-sidebar">BUESMIS</span>
                    <span class="-mt-1 block text-sm text-fleet-muted">Busitema University Fleet Services</span>
                </span>
            </a>

            <nav class="hidden items-center gap-8 text-[15px] font-semibold text-fleet-ink lg:flex">
                <a href="#overview" class="hover:text-fleet-primary">Overview</a>
                <a href="#services" class="hover:text-fleet-primary">Services</a>
                <?php if ($homeUserIsLoggedIn): ?>
                    <a href="#vehicles" class="hover:text-fleet-primary">Vehicles</a>
                <?php endif; ?>
                <a href="#request" class="hover:text-fleet-primary">Request</a>
                <a href="#support" class="hover:text-fleet-primary">Support</a>
            </nav>

            <div class="hidden items-center gap-3 sm:flex">
                <a href="<?= htmlspecialchars($loginUrl, ENT_QUOTES, 'UTF-8'); ?>" class="inline-flex rounded-xl border border-fleet-line bg-white/95 px-4 py-3 text-sm font-bold text-fleet-ink shadow-sm backdrop-blur hover:bg-white">
                    Staff Login
                </a>
                <a href="#request" class="inline-flex rounded-xl bg-fleet-primary px-5 py-3 font-bold text-white shadow-sm hover:bg-fleet-primary-strong">
                    Request Vehicle
                </a>
            </div>
        </div>
    </header>

    <section class="landing-hero text-white">
        <div class="w-full pt-0 pb-6">
            <div
                class="landing-hero-shell overflow-hidden"
                style="background-image: linear-gradient(90deg, rgba(3, 9, 22, 0.18) 0%, rgba(3, 9, 22, 0.08) 100%), url('<?= htmlspecialchars($heroImageUrl, ENT_QUOTES, 'UTF-8'); ?>');"
            >
                <div class="landing-hero-overlay"></div>

                <div class="relative z-[2] mx-auto flex min-h-[520px] max-w-7xl flex-col justify-between px-5 pt-24 pb-6 sm:px-6 sm:pt-26 lg:min-h-[700px] lg:px-8 lg:pt-28 lg:pb-10">
                    <div class="flex justify-end">
                        <div class="landing-hero-copy text-right text-[1.6rem] font-medium italic leading-[1.45] text-white/95 sm:text-[1.9rem]">
                            <p>Reliable Vehicles.</p>
                            <p class="text-[#1d9bff]">Efficient Movement.</p>
                            <p>Better University.</p>
                        </div>
                    </div>

                    <div class="max-w-[500px]">
                        <h1 class="max-w-[11ch] text-4xl font-black uppercase leading-[0.95] tracking-tight text-white sm:text-[4rem] lg:text-[3.85rem]">
                            <span class="block">Smart Fleet</span>
                            <span class="block text-[#1d9bff]">For A Smarter</span>
                            <span class="block">University</span>
                        </h1>

                        <div class="mt-5 h-1 w-24 rounded-full bg-fleet-primary"></div>

                        <p class="mt-5 max-w-lg text-base leading-7 text-slate-200 sm:text-base sm:leading-7 lg:text-[0.98rem] lg:leading-7">
                            BUESMIS ensures reliable, efficient and transparent transport management
                            across all university operations.
                        </p>

                        <div class="mt-6 flex flex-col gap-4 sm:flex-row">
                            <a href="#request" class="inline-flex items-center justify-center rounded-xl bg-fleet-primary px-7 py-3.5 text-base font-extrabold text-white shadow-lg hover:bg-fleet-primary-strong">
                                Make a Request
                            </a>
                            <?php if ($homeUserIsLoggedIn): ?>
                                <a href="#vehicles" class="inline-flex items-center justify-center rounded-xl border border-white/20 bg-white/5 px-7 py-3.5 text-base font-extrabold text-white backdrop-blur hover:bg-white/10">
                                    View Vehicles
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="landing-hero-stats mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                        <article class="landing-hero-stat">
                            <span class="landing-hero-stat-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M5 16l1.4-4.4A3 3 0 0 1 9.25 9.5h5.5a3 3 0 0 1 2.85 2.1L19 16"></path>
                                    <path d="M4.5 16.5h15a1.5 1.5 0 0 1 1.5 1.5v1h-2.5"></path>
                                    <path d="M3 19v-1a1.5 1.5 0 0 1 1.5-1.5"></path>
                                    <circle cx="7.5" cy="18.5" r="1.5"></circle>
                                    <circle cx="16.5" cy="18.5" r="1.5"></circle>
                                </svg>
                            </span>
                            <p class="text-4xl font-black text-white">30+</p>
                            <p class="mt-2 text-base font-semibold text-slate-200">Fleet Vehicles</p>
                        </article>
                        <article class="landing-hero-stat">
                            <span class="landing-hero-stat-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="9" cy="8" r="3"></circle>
                                    <circle cx="17" cy="9" r="2.5"></circle>
                                    <path d="M3.5 18.5a5.5 5.5 0 0 1 11 0"></path>
                                    <path d="M14 18.5a4.5 4.5 0 0 1 7 0"></path>
                                </svg>
                            </span>
                            <p class="text-4xl font-black text-white">200+</p>
                            <p class="mt-2 text-base font-semibold text-slate-200">Authorized Users</p>
                        </article>
                        <article class="landing-hero-stat">
                            <span class="landing-hero-stat-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M12 21s6-5.2 6-11a6 6 0 1 0-12 0c0 5.8 6 11 6 11Z"></path>
                                    <circle cx="12" cy="10" r="2.4"></circle>
                                </svg>
                            </span>
                            <p class="text-4xl font-black text-white">All</p>
                            <p class="mt-2 text-base font-semibold text-slate-200">Campus Coverage</p>
                        </article>
                        <article class="landing-hero-stat">
                            <span class="landing-hero-stat-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M12 3l7 3v5c0 4.6-2.7 8.3-7 10-4.3-1.7-7-5.4-7-10V6l7-3Z"></path>
                                    <path d="m9.5 11.8 1.7 1.7 3.8-4"></path>
                                </svg>
                            </span>
                            <p class="text-4xl font-black text-white">100%</p>
                            <p class="mt-2 text-base font-semibold text-slate-200">Accountability</p>
                        </article>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="overview" class="landing-grid">
        <div class="mx-auto max-w-7xl px-5 py-20 sm:px-6 lg:px-8">
            <div class="grid items-center gap-12 rounded-[2rem] border border-fleet-line-soft bg-white p-8 shadow-sm lg:grid-cols-2 lg:p-12">
                <div>
                    <p class="text-sm font-black uppercase tracking-wide text-fleet-primary">System Overview</p>
                    <h2 class="mt-3 text-3xl font-black leading-tight text-fleet-ink sm:text-4xl">
                        A practical transport system for university operations
                    </h2>
                    <p class="mt-5 text-lg leading-8 text-fleet-muted">
                        BUESMIS brings together transport coordination, vehicle records, driver allocation,
                        pre-trip inspections, post-trip follow-up, maintenance scheduling, service provider
                        coordination, fuel and mileage records, and estates operations tracking.
                    </p>
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <article class="rounded-[1.25rem] border border-fleet-line-soft bg-fleet-surface-muted p-6">
                        <h3 class="text-xl font-black text-fleet-sidebar">Accountability</h3>
                        <p class="mt-2 text-fleet-muted">Clear trip records, approvals, driver activity, and vehicle usage history.</p>
                    </article>
                    <article class="rounded-[1.25rem] border border-fleet-line-soft bg-fleet-surface-muted p-6">
                        <h3 class="text-xl font-black text-fleet-sidebar">Maintenance</h3>
                        <p class="mt-2 text-fleet-muted">Track inspections, servicing, defects, repairs, and follow-up actions.</p>
                    </article>
                    <article class="rounded-[1.25rem] border border-fleet-line-soft bg-fleet-surface-muted p-6">
                        <h3 class="text-xl font-black text-fleet-sidebar">Planning</h3>
                        <p class="mt-2 text-fleet-muted">Support organized transport scheduling for departments and field work.</p>
                    </article>
                    <article class="rounded-[1.25rem] border border-fleet-line-soft bg-fleet-surface-muted p-6">
                        <h3 class="text-xl font-black text-fleet-sidebar">Reporting</h3>
                        <p class="mt-2 text-fleet-muted">Generate useful summaries for transport, fuel, mileage, and operations.</p>
                    </article>
                </div>
            </div>
        </div>
    </section>

    <section id="services" class="bg-white">
        <div class="mx-auto max-w-7xl px-5 py-20 sm:px-6 lg:px-8">
            <div class="max-w-3xl">
                <p class="text-sm font-black uppercase tracking-wide text-fleet-primary">What the system supports</p>
                <h2 class="mt-3 text-3xl font-black sm:text-4xl">Fleet services built around university needs</h2>
                <p class="mt-4 text-lg leading-8 text-fleet-muted">
                    BUESMIS is designed for structured university transport administration, with public-facing
                    guidance for requests and restricted operational access for staff.
                </p>
            </div>

            <div class="mt-12 grid gap-7 md:grid-cols-2 xl:grid-cols-3">
                <article class="landing-service-card">
                    <div class="flex h-12 w-12 items-center justify-center rounded-[1rem] bg-fleet-primary-soft text-xl font-black text-fleet-primary">01</div>
                    <h3 class="mt-5 text-xl font-black text-fleet-sidebar">Vehicle Management</h3>
                    <p class="mt-3 leading-7 text-fleet-muted">Fleet registry, status monitoring, allocation records, ownership details, and usage visibility.</p>
                </article>

                <article class="landing-service-card">
                    <div class="flex h-12 w-12 items-center justify-center rounded-[1rem] bg-[#e9f7ff] text-xl font-black text-[#0284c7]">02</div>
                    <h3 class="mt-5 text-xl font-black text-fleet-sidebar">Driver &amp; Trip Tracking</h3>
                    <p class="mt-3 leading-7 text-fleet-muted">Driver assignments, trip schedules, destinations, logbooks, mileage, and trip completion records.</p>
                </article>

                <article class="landing-service-card">
                    <div class="flex h-12 w-12 items-center justify-center rounded-[1rem] bg-fleet-success-soft text-xl font-black text-fleet-success">03</div>
                    <h3 class="mt-5 text-xl font-black text-fleet-sidebar">Inspections &amp; Maintenance</h3>
                    <p class="mt-3 leading-7 text-fleet-muted">Pre-trip checks, defect reporting, maintenance follow-up, servicing, and provider coordination.</p>
                </article>

                <article class="landing-service-card">
                    <div class="flex h-12 w-12 items-center justify-center rounded-[1rem] bg-fleet-warning-soft text-xl font-black text-fleet-warning-strong">04</div>
                    <h3 class="mt-5 text-xl font-black text-fleet-sidebar">Fuel &amp; Mileage Records</h3>
                    <p class="mt-3 leading-7 text-fleet-muted">Track fuel consumption, mileage movement, trip efficiency, and vehicle operational cost indicators.</p>
                </article>

                <article class="landing-service-card">
                    <div class="flex h-12 w-12 items-center justify-center rounded-[1rem] bg-fleet-primary-soft text-xl font-black text-fleet-primary">05</div>
                    <h3 class="mt-5 text-xl font-black text-fleet-sidebar">Estates &amp; Works Support</h3>
                    <p class="mt-3 leading-7 text-fleet-muted">Coordinate vehicles used for estates, field support, works movement, materials, and maintenance tasks.</p>
                </article>

                <article class="landing-service-card">
                    <div class="flex h-12 w-12 items-center justify-center rounded-[1rem] bg-fleet-danger-soft text-xl font-black text-fleet-danger">06</div>
                    <h3 class="mt-5 text-xl font-black text-fleet-sidebar">Reports &amp; Communication</h3>
                    <p class="mt-3 leading-7 text-fleet-muted">Operational summaries, transport planning reports, request updates, and service communication.</p>
                </article>
            </div>
        </div>
    </section>

    <?php if ($homeUserIsLoggedIn): ?>
        <section id="vehicles" class="landing-showcase-section">
            <div class="mx-auto max-w-[1380px] px-5 py-20 sm:px-6 lg:px-8">
                <div class="text-center">
                    <p class="text-sm font-black uppercase tracking-[0.18em] text-fleet-primary">BUESMIS Fleet</p>
                    <h2 class="mt-3 text-3xl font-black text-fleet-sidebar sm:text-4xl lg:text-5xl">Available Vehicles</h2>
                    <p class="mx-auto mt-4 max-w-2xl text-base leading-7 text-fleet-muted sm:text-lg">
                        Explore the university transport fleet through a more realistic operational showcase before making a formal request.
                    </p>
                </div>

                <div class="landing-showcase-shell mt-12" data-fleet-showcase>
                    <div class="landing-showcase-layout">
                        <div class="landing-showcase-stage">
                            <img
                                src="assets/images/fleet-showcase/prado-garage.png"
                                alt="Toyota Prado in BUESMIS showcase garage"
                                class="landing-showcase-image is-visible"
                                data-vehicle-image
                            >

                            <div class="landing-showcase-overlay" aria-hidden="true"></div>

                            <aside class="landing-showcase-panel">
                                <div>
                                    <p class="text-xs font-black uppercase tracking-[0.18em] text-fleet-primary">Operational Profile</p>
                                    <h3 class="mt-3 text-2xl font-black text-white sm:text-[2rem]" data-vehicle-model>Toyota Prado</h3>
                                    <p class="mt-3 text-sm leading-7 text-slate-300" data-vehicle-description>
                                        Reliable field-ready transport for official university movement, inspections, and administrative duty.
                                    </p>
                                </div>

                                <div class="mt-5">
                                    <span class="landing-status-pill" data-vehicle-status>Available</span>
                                </div>

                                <dl class="mt-6 space-y-3 text-sm">
                                    <div class="landing-spec-row">
                                        <dt>Registration</dt>
                                        <dd data-vehicle-registration>UBQ 123C</dd>
                                    </div>
                                    <div class="landing-spec-row">
                                        <dt>Vehicle Type</dt>
                                        <dd data-vehicle-type>Administrative SUV</dd>
                                    </div>
                                    <div class="landing-spec-row">
                                        <dt>Capacity</dt>
                                        <dd data-vehicle-capacity>7 Seater</dd>
                                    </div>
                                    <div class="landing-spec-row">
                                        <dt>Usage</dt>
                                        <dd data-vehicle-usage>Executive field coordination</dd>
                                    </div>
                                </dl>

                                <a href="#request" class="mt-7 inline-flex items-center justify-center rounded-xl bg-fleet-primary px-5 py-3 text-sm font-black text-white shadow-lg hover:bg-fleet-primary-strong">
                                    Make a Request
                                </a>
                            </aside>

                            <button
                                type="button"
                                class="landing-showcase-arrow landing-showcase-arrow-left"
                                aria-label="Previous vehicle"
                                data-vehicle-prev
                                onclick="previousVehicle()"
                            >
                                <span aria-hidden="true">&#8249;</span>
                            </button>

                            <button
                                type="button"
                                class="landing-showcase-arrow landing-showcase-arrow-right"
                                aria-label="Next vehicle"
                                data-vehicle-next
                                onclick="nextVehicle()"
                            >
                                <span aria-hidden="true">&#8250;</span>
                            </button>

                            <div class="landing-showcase-footer">
                                <div class="landing-showcase-meta">
                                    <p class="text-xs font-black uppercase tracking-[0.18em] text-fleet-primary">Current Vehicle</p>
                                    <p class="mt-2 text-lg font-black text-white" data-vehicle-subtitle>Toyota Prado</p>
                                    <p class="mt-1 text-sm text-slate-300" data-vehicle-caption>Administrative SUV</p>
                                </div>

                                <div class="landing-showcase-dots" data-vehicle-dots aria-label="Vehicle slider indicators"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <?php if ($homeCanViewRequestForm): ?>
        <section id="request" class="bg-white">
            <div class="mx-auto grid max-w-7xl gap-12 px-5 py-20 sm:px-6 lg:grid-cols-2 lg:px-8">
                <div>
                    <p class="text-sm font-black uppercase tracking-wide text-fleet-primary">Vehicle request</p>
                    <h2 class="mt-3 text-3xl font-black leading-tight sm:text-4xl">Request transport for official university work</h2>
                    <p class="mt-5 text-lg leading-8 text-fleet-muted">
                        Departments, staff, and authorized units can submit vehicle requests for approved academic,
                        administrative, field, estates, and operational activities.
                    </p>

                    <div class="mt-8 rounded-[1.5rem] border border-fleet-line-soft bg-fleet-surface-muted p-7">
                        <h3 class="text-xl font-black text-fleet-sidebar">Why BUESMIS matters</h3>
                        <ul class="mt-5 space-y-4 text-fleet-muted">
                            <li class="flex gap-3"><span class="font-black text-fleet-success">+</span> Improves visibility over vehicle movement and usage</li>
                            <li class="flex gap-3"><span class="font-black text-fleet-success">+</span> Strengthens accountability through proper records</li>
                            <li class="flex gap-3"><span class="font-black text-fleet-success">+</span> Supports preventive maintenance and service follow-up</li>
                            <li class="flex gap-3"><span class="font-black text-fleet-success">+</span> Helps plan transport for university operations efficiently</li>
                        </ul>
                    </div>
                </div>

                <form action="<?= htmlspecialchars($vehicleRequestFormAction, ENT_QUOTES, 'UTF-8'); ?>" method="post" data-fleet-ajax="true" data-fleet-reset-on-success="true" class="rounded-[2rem] border border-fleet-line-soft bg-fleet-surface-muted p-7 shadow-sm sm:p-9">
                    <input type="hidden" name="vehicle_request_action" value="create">
                    <div data-fleet-feedback-host></div>

                    <?php if (!empty($vehicleRequestNotification)): ?>
                        <?php $isVehicleRequestSuccess = ($vehicleRequestNotification['type'] ?? '') === 'success'; ?>
                        <div class="mb-5 rounded-2xl border px-4 py-4 text-sm leading-6 <?= $isVehicleRequestSuccess ? 'border-green-200 bg-green-50 text-green-900' : 'border-red-200 bg-red-50 text-red-900'; ?>">
                            <p class="font-extrabold uppercase tracking-[0.18em]"><?= htmlspecialchars($vehicleRequestNotification['title'] ?? 'Vehicle request update', ENT_QUOTES, 'UTF-8'); ?></p>
                            <p class="mt-2"><?= htmlspecialchars($vehicleRequestNotification['message'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
                        </div>
                    <?php endif; ?>

                    <div class="grid gap-5 sm:grid-cols-2">
                        <label class="block">
                            <span class="font-bold text-fleet-ink">Full Name</span>
                            <input name="full_name" required class="landing-form-control mt-2" placeholder="Enter full name" value="<?= htmlspecialchars($vehicleRequestFormData['full_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        </label>

                        <label class="block">
                            <span class="font-bold text-fleet-ink">Department</span>
                            <input name="department" required class="landing-form-control mt-2" placeholder="Department / Unit" value="<?= htmlspecialchars($vehicleRequestFormData['department'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        </label>

                        <label class="block">
                            <span class="font-bold text-fleet-ink">Email Address</span>
                            <input name="email_address" type="email" required class="landing-form-control mt-2" placeholder="name@busitema.ac.ug" value="<?= htmlspecialchars($vehicleRequestFormData['email_address'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        </label>

                        <label class="block">
                            <span class="font-bold text-fleet-ink">Phone Number</span>
                            <input name="phone_number" required class="landing-form-control mt-2" placeholder="+256..." value="<?= htmlspecialchars($vehicleRequestFormData['phone_number'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        </label>

                        <label class="block">
                            <span class="font-bold text-fleet-ink">Job Title / Role</span>
                            <input name="job_title" required class="landing-form-control mt-2" placeholder="e.g. Lecturer, Administrator" value="<?= htmlspecialchars($vehicleRequestFormData['job_title'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        </label>

                        <label class="block">
                            <span class="font-bold text-fleet-ink">Trip Destination</span>
                            <input name="trip_destination" required class="landing-form-control mt-2" placeholder="Destination" value="<?= htmlspecialchars($vehicleRequestFormData['trip_destination'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        </label>

                        <label class="block">
                            <span class="font-bold text-fleet-ink">Request Date</span>
                            <input name="request_date" type="date" required class="landing-form-control mt-2" value="<?= htmlspecialchars($vehicleRequestFormData['request_date'] ?? date('Y-m-d'), ENT_QUOTES, 'UTF-8'); ?>">
                        </label>

                        <label class="block">
                            <span class="font-bold text-fleet-ink">Preferred Vehicle Type</span>
                            <select name="preferred_vehicle_type" class="landing-form-control mt-2">
                                <option value="">Select vehicle type</option>
                                <option value="Staff Van" <?= (($vehicleRequestFormData['preferred_vehicle_type'] ?? '') === 'Staff Van') ? 'selected' : ''; ?>>Staff Van</option>
                                <option value="Administrative SUV" <?= (($vehicleRequestFormData['preferred_vehicle_type'] ?? '') === 'Administrative SUV') ? 'selected' : ''; ?>>Administrative SUV</option>
                                <option value="Departmental Car" <?= (($vehicleRequestFormData['preferred_vehicle_type'] ?? '') === 'Departmental Car') ? 'selected' : ''; ?>>Departmental Car</option>
                                <option value="Works Truck" <?= (($vehicleRequestFormData['preferred_vehicle_type'] ?? '') === 'Works Truck') ? 'selected' : ''; ?>>Works Truck</option>
                            </select>
                        </label>

                        <label class="block">
                            <span class="font-bold text-fleet-ink">Purpose of Trip</span>
                            <input name="purpose_of_trip" required class="landing-form-control mt-2" placeholder="Official purpose" value="<?= htmlspecialchars($vehicleRequestFormData['purpose_of_trip'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        </label>

                        <label class="block sm:col-span-2">
                            <span class="font-bold text-fleet-ink">Reason for Request</span>
                            <textarea name="reason" required class="landing-form-control mt-2 min-h-32 resize-y py-3" placeholder="Explain why this transport is needed for the activity."><?= htmlspecialchars($vehicleRequestFormData['reason'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                        </label>
                    </div>

                    <button type="submit" data-loading-text="Submitting Request..." class="mt-7 w-full rounded-xl bg-fleet-primary px-7 py-4 font-black text-white shadow-sm hover:bg-fleet-primary-strong">
                        Submit Vehicle Request
                    </button>

                    <p class="mt-4 text-center text-sm text-fleet-muted">
                        Requests submitted here are sent to the transport office for admin review and follow-up.
                    </p>

                    <div class="mt-5 text-center text-sm text-fleet-muted">
                        Staff can continue to the
                        <a href="<?= htmlspecialchars($loginUrl, ENT_QUOTES, 'UTF-8'); ?>" class="font-bold text-fleet-primary hover:text-fleet-primary-strong">login page</a>
                        or
                        <a href="<?= htmlspecialchars($dashboardUrl, ENT_QUOTES, 'UTF-8'); ?>" class="font-bold text-fleet-primary hover:text-fleet-primary-strong">dashboard</a>.
                    </div>
                </form>
            </div>
        </section>
    <?php endif; ?>

    <footer id="support" class="bg-fleet-sidebar text-white">
        <div class="mx-auto grid max-w-7xl gap-10 px-5 py-14 sm:px-6 md:grid-cols-3 lg:px-8">
            <div>
                <h2 class="text-2xl font-black">BUESMIS</h2>
                <p class="mt-4 leading-7 text-blue-100">
                    Busitema University Fleet Management System for transport coordination,
                    maintenance visibility, and accountable university operations.
                </p>
            </div>

            <div>
                <h3 class="text-lg font-black">Quick Links</h3>
                <ul class="mt-4 space-y-3 text-blue-100">
                    <li><a href="#overview" class="hover:text-white">System Overview</a></li>
                    <li><a href="#services" class="hover:text-white">Fleet Services</a></li>
                    <?php if ($homeUserIsLoggedIn): ?>
                        <li><a href="#vehicles" class="hover:text-white">Available Vehicles</a></li>
                    <?php endif; ?>
                    <?php if ($homeCanViewRequestForm): ?>
                        <li><a href="#request" class="hover:text-white">Request a Vehicle</a></li>
                    <?php endif; ?>
                </ul>
            </div>

            <div>
                <h3 class="text-lg font-black">Contact / Support</h3>
                <p class="mt-4 leading-7 text-blue-100">
                    Estates &amp; Transport Department<br>
                    Busitema University<br>
                    support@buesmis.ac.ug
                </p>
            </div>
        </div>

        <div class="border-t border-white/10">
            <div class="mx-auto flex max-w-7xl flex-col justify-between gap-2 px-5 py-5 text-sm text-blue-100 sm:flex-row sm:px-6 lg:px-8">
                <p>&copy; 2026 Busitema University Fleet Management System</p>
                <p>Public Fleet &amp; Transport Services Portal</p>
            </div>
        </div>
    </footer>
</main>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const homeHeader = document.querySelector('[data-home-header]');

    if (!homeHeader) {
        return;
    }

    const syncHomeHeaderStickyState = function () {
        const shouldStick = window.scrollY > homeHeader.offsetHeight;

        homeHeader.classList.toggle('absolute', !shouldStick);
        homeHeader.classList.toggle('fixed', shouldStick);
        homeHeader.classList.toggle('shadow-lg', shouldStick);
        homeHeader.classList.toggle('bg-white', shouldStick);
        homeHeader.classList.toggle('bg-white/95', !shouldStick);
    };

    window.addEventListener('scroll', syncHomeHeaderStickyState, { passive: true });
    window.addEventListener('resize', syncHomeHeaderStickyState);
    syncHomeHeaderStickyState();
});
</script>
<?php include __DIR__ . '/includes/footer.php'; ?>
