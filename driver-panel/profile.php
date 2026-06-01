<?php
$activePage = 'driver-profile';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>
<main class="min-h-screen lg:pl-64">
    <div class="mx-auto max-w-[1320px] px-4 py-6 sm:px-6 lg:px-8">
        <div class="mb-6 flex items-start justify-between gap-4">
            <div>
                <h1 class="text-2xl font-extrabold tracking-normal text-fleet-ink sm:text-3xl">Profile</h1>
                <p class="mt-1 text-sm text-fleet-muted">Driver account and personal details</p>
            </div>
            <button id="sidebar-toggle" class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-fleet-line bg-fleet-surface text-fleet-ink shadow-fleet-card lg:hidden" type="button" aria-label="Open navigation">
                <span class="text-xl leading-none">&#9776;</span>
            </button>
        </div>

        <section class="rounded-lg border border-fleet-line bg-fleet-surface p-6 shadow-fleet-card">
            <h2 class="text-lg font-extrabold text-fleet-ink">Profile Page</h2>
            <p class="mt-2 text-sm text-fleet-muted">This page is scaffolded and ready for driver profile features.</p>
        </section>
    </div>
</main>
<?php include __DIR__ . '/../includes/footer.php'; ?>
