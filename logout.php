<?php
session_start();
session_unset();
session_destroy();
?>
<script>
    alert('You have logged out');
    window.location.href = 'login.php';
</script>
