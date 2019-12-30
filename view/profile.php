<?php
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　プロフィール　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

require('auth.php');

$formData = getUserData($_SESSION['user_id']);

?>
<!DOCTYPE html>
<html lang="en">

<?php
$title = 'プロフィール';
require('head.php');
?>

<body>
<header></header>

<main>
<div>
<?php echo $formData['first_name']; ?>
</div>
<div>
<?php echo $formData['last_name']; ?>
</div>
<div>
<?php echo $formData['eamil']; ?>
</div>
</main>

</body>
</html>