<?php
// Shared footer partial for closing layout markup and future JavaScript includes.
?>
    <script src="<?= htmlspecialchars($scriptPath ?? (($basePath ?? '') . '/assets/js/app.js'), ENT_QUOTES, 'UTF-8'); ?>"></script>
    <script src="<?= htmlspecialchars($moduleScriptPath ?? (($basePath ?? '') . '/assets/js/module-modals.js'), ENT_QUOTES, 'UTF-8'); ?>"></script>
</body>
</html>
