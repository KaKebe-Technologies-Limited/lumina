<?php
/**
 * SHOP catalogue (/shop and /shop/category/{slug}). OWNER: STOREFRONT. Vars: $items (hydrated products), $catalogue (page, pages, total, perPage, sort), $categories,
 * $category (current or null), $q, $open. Every word: content('shop', ...); every price: the hydrated product; every rule: ProductService.
 */
use App\Services\Shop\CartService;
use App\Services\Shop\ShopSettings;
use App\Services\Web\TextTokens;

$this->start('scripts'); ?>
<script src="<?= e(asset('js/shop.js')) ?>" defer></script>
<?php $this->end();
$hero = content_img('shop', 'hero.image');
$heroBtn = content_btn('shop', 'hero.cta');
$sorts = ['featured' => 'sort_featured', 'newest' => 'sort_newest', 'price_asc' => 'sort_price_asc', 'price_desc' => 'sort_price_desc', 'name' => 'sort_name'];
$formAction = $category ? url('shop/category/' . $category['slug']) : url('shop');
$cartUnits = CartService::units();
$filtered = $q !== '' || $category !== null;
$trust = content_list('shop', 'trust.items');
$faqs = array_map(static fn(array $f): array => ['question' => TextTokens::text((string) ($f['question'] ?? '')), 'answer' => TextTokens::text((string) ($f['answer'] ?? ''))], content_list('shop', 'faq.items'));
$fund = section_on('shop', 'fundraising');
$fundBtn = content_btn('shop', 'fundraising.cta');
?>
<?= partial('partials/page-hero', [
    'title'   => $category ? (string) $category['name'] : (string) content('shop', 'hero.title'),
    'eyebrow' => (string) content('shop', 'hero.eyebrow'),
    'lead'    => (string) content('shop', 'hero.lead'),
    'image'   => $hero['has'] ? ['src' => $hero['src'], 'alt' => $hero['alt'], 'focus' => '50% 40%'] : null,
    'actions' => $heroBtn['has'] ? [['label' => $heroBtn['label'], 'url' => $heroBtn['url'], 'class' => 'btn--cta btn--caps']] : [],
    'sheet'   => 'white',
]) ?>

<section class="section sh-catalogue" data-no-justify id="products" aria-labelledby="sh-products-title">
    <div class="container">
        <?php if (!$open): ?>
            <?php $closedBtn = content_btn('shop', 'closed.cta'); ?>
            <div class="sh-closed" role="status">
                <span class="sh-closed__icon"><?= icon('clock', 'icon--lg') ?></span>
                <div class="sh-closed__text">
                    <strong class="sh-closed__title"><?= e(content('shop', 'closed.title')) ?></strong>
                    <p><?= e(ShopSettings::closedMessage()) ?></p>
                </div>
                <?php if ($closedBtn['has']): ?><a class="btn btn--outline btn--caps btn--sm" href="<?= e($closedBtn['url']) ?>"><?= e($closedBtn['label']) ?></a><?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="sh-head">
            <?= partial('partials/section-head', ['title' => (string) content('shop', 'catalogue.title'), 'id' => 'sh-products-title', 'class' => 'mb-0']) ?>
            <?php if ($cartUnits > 0): ?>
                <a class="btn btn--outline btn--caps btn--sm sh-head__cart" href="<?= e(url('shop/cart')) ?>"><?= icon('shopping-cart', 'icon--sm') ?> <?= e(content('shop', 'catalogue.view_cart_label')) ?> <span class="sh-count" data-shop-count><?= (int) $cartUnits ?></span></a>
            <?php endif; ?>
        </div>

        <nav class="sh-chips" aria-label="<?= e(content('shop', 'catalogue.categories_label')) ?>">
            <ul class="pills">
                <li><a class="pills__item<?= $category === null ? ' is-active' : '' ?>" href="<?= e(url('shop')) ?>"<?= $category === null ? ' aria-current="page"' : '' ?>><?= e(content('shop', 'catalogue.all_label')) ?></a></li>
                <?php foreach ($categories as $c): $on = $category !== null && $category['slug'] === $c['slug']; ?>
                    <li><a class="pills__item<?= $on ? ' is-active' : '' ?>" href="<?= e(url('shop/category/' . $c['slug'])) ?>"<?= $on ? ' aria-current="page"' : '' ?>><?= e($c['name']) ?></a></li>
                <?php endforeach; ?>
            </ul>
        </nav>

        <form class="sh-tools" method="get" action="<?= e($formAction) ?>" role="search">
            <div class="sh-tools__search">
                <label class="visually-hidden" for="shop-q"><?= e(content('shop', 'catalogue.search_label')) ?></label>
                <span class="sh-tools__icon" aria-hidden="true"><?= icon('search', 'icon--sm') ?></span>
                <input class="input" id="shop-q" type="search" name="q" value="<?= e($q) ?>" maxlength="100" placeholder="<?= e(content('shop', 'catalogue.search_placeholder')) ?>" autocomplete="off">
                <button class="btn btn--primary btn--sm btn--caps" type="submit"><?= e(content('shop', 'catalogue.search_button')) ?></button>
            </div>
            <div class="sh-tools__sort">
                <label for="shop-sort"><?= e(content('shop', 'catalogue.sort_label')) ?></label>
                <select class="select" id="shop-sort" name="sort">
                    <?php foreach ($sorts as $k => $labelKey): ?>
                        <option value="<?= e($k) ?>"<?= $catalogue['sort'] === $k ? ' selected' : '' ?>><?= e(content('shop', 'catalogue.' . $labelKey)) ?></option>
                    <?php endforeach; ?>
                </select>
                <button class="btn btn--outline btn--sm" type="submit"><?= e(content('shop', 'catalogue.sort_apply')) ?></button>
            </div>
        </form>
        <p class="sh-count-line"><?= e(str_replace('{n}', (string) $catalogue['total'], (string) content('shop', $catalogue['total'] === 1 ? 'catalogue.results_one' : 'catalogue.results_many'))) ?>
            <?php if ($filtered): ?> <a class="sh-clear" href="<?= e(url('shop')) ?>"><?= e(content('shop', 'catalogue.clear_label')) ?></a><?php endif; ?></p>

        <?php if ($items === []): ?>
            <?= partial('partials/empty-state', ['title' => (string) content('shop', $filtered ? 'catalogue.empty_filtered_title' : 'catalogue.empty_title'), 'icon' => 'shopping-bag', 'text' => (string) content('shop', $filtered ? 'catalogue.empty_filtered_text' : 'catalogue.empty_text'),
                'actions' => $filtered ? [['label' => (string) content('shop', 'catalogue.clear_label'), 'url' => url('shop'), 'class' => 'btn--primary']] : []]) ?>
        <?php else: ?>
            <div class="sh-grid">
                <?php foreach ($items as $i => $p): ?>
                    <?= partial('partials/shop-card', ['p' => $p, 'open' => $open, 'priority' => $i < 2, 'heading' => 'h3']) ?>
                <?php endforeach; ?>
            </div>
            <?= partial('partials/pagination', ['pagination' => \App\Core\Pagination::make($catalogue['total'], $catalogue['page'], $catalogue['perPage'])]) ?>
        <?php endif; ?>
        <div class="visually-hidden" role="status" aria-live="polite" data-shop-live></div>
    </div>
</section>

<?php if ($fund): ?>
<section class="section section--navy sh-mission" data-no-justify aria-labelledby="sh-mission-title">
    <div class="container sh-mission__inner">
        <div class="sh-mission__text">
            <?php if (trim((string) content('shop', 'fundraising.eyebrow')) !== ''): ?><span class="eyebrow"><?= e(content('shop', 'fundraising.eyebrow')) ?></span><?php endif; ?>
            <h2 id="sh-mission-title"><?= highlight((string) content('shop', 'fundraising.title')) ?></h2>
            <p><?= e(content('shop', 'fundraising.text')) ?></p>
        </div>
        <?php if ($fundBtn['has']): ?><a class="btn btn--gold btn--caps" href="<?= e($fundBtn['url']) ?>"><?= e($fundBtn['label']) ?></a><?php endif; ?>
    </div>
</section>
<?php endif; ?>

<?php if (section_on('shop', 'trust') && $trust !== []): ?>
<section class="section section--tint sh-trust" aria-label="<?= e(content('shop', 'hero.eyebrow')) ?>">
    <div class="container">
        <div class="feature-row">
            <?php foreach ($trust as $t): ?>
                <?= partial('partials/feature-card', ['icon' => safe_icon((string) ($t['icon'] ?? '')), 'title' => (string) ($t['title'] ?? ''), 'text' => (string) ($t['text'] ?? '')]) ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if (section_on('shop', 'faq') && $faqs !== []): ?>
<section class="section sh-faq" aria-labelledby="sh-faq-title">
    <div class="container container--narrow">
        <?= partial('partials/faq-split', ['faqs' => $faqs, 'title' => (string) content('shop', 'faq.title'), 'prefix' => 'shopfaq', 'first_open' => false, 'id' => 'sh-faq-title']) ?>
    </div>
</section>
<?php endif; ?>
