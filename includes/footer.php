<?php
// Shared footer partial for closing layout markup and loading shared JavaScript.
?>
    <!-- jQuery is loaded once globally so shared UX helpers can enhance existing PHP forms. -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="<?= htmlspecialchars($scriptPath ?? (($basePath ?? '') . '/assets/js/app.js'), ENT_QUOTES, 'UTF-8'); ?>"></script>
    <script src="<?= htmlspecialchars($moduleScriptPath ?? (($basePath ?? '') . '/assets/js/module-modals.js'), ENT_QUOTES, 'UTF-8'); ?>"></script>
    <!-- Shared Fleet jQuery helpers are loaded after the core scripts to avoid duplicate logic. -->
    <script src="<?= htmlspecialchars($jqueryFleetScriptPath ?? (($basePath ?? '') . '/assets/js/fleet-jquery.js'), ENT_QUOTES, 'UTF-8'); ?>"></script>
</body>
</html>
