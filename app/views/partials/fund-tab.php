<?php
declare(strict_types=1);
/**
 * A slim gold tab on the right edge of every public page: "Fund a project", one tap to the project funding page (/fund-a-project, the four costed proposals).
 * Right edge, middle of the screen, so it clears Bono (bottom left) and the translate / back-to-top buttons (bottom right). Hidden on the funding pages themselves.
 * Pure CSS (scss/pages/_fund.scss, .fund-tab); the soft pulse stops for people who ask for reduced motion.
 */
$path = (string) parse_url((string) ($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH);
if (str_contains($path, 'fund-a-project')) {
    return;
}
?>
<a class="fund-tab" href="<?= e(url('fund-a-project')) ?>" aria-label="Fund a project: four costed projects, from USD 5"><?= icon('heart', 'icon--sm') ?><span class="fund-tab__text">Fund a project</span></a>
