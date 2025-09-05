<?php include("header.php"); ?>
<?php $page= "Our Brands"; ?>

    <main class="mx-auto pt-[112px] md:pt-[112px]">
        <?php include("inc/breadcrumb.php"); ?>

        <?php
            // Update these paths to your actual files (root-relative recommended)
            $brands = [
            [ 'name' => 'BAW',             'logo' => '/assets/images/brands/baw.png',        'pdf' => '/assets/pdfs/baw.pdf' ],
            [ 'name' => 'Bosch',           'logo' => '/assets/images/brands/bosch.png',      'pdf' => '/assets/pdfs/bosch.pdf' ],
            [ 'name' => 'Crompton Greaves','logo' => '/assets/images/brands/crompton.png',   'pdf' => null ],
            [ 'name' => 'DeWALT',          'logo' => '/assets/images/brands/dewalt.png',     'pdf' => '/assets/pdfs/dewalt.pdf' ],
            [ 'name' => 'Endico',          'logo' => '/assets/images/brands/endico.png',     'pdf' => '/assets/pdfs/endico.pdf' ],
            [ 'name' => 'Hitachi',         'logo' => '/assets/images/brands/hitachi.png',    'pdf' => '/assets/pdfs/hitachi.pdf' ],
            [ 'name' => 'Indef',           'logo' => '/assets/images/brands/indef.png',      'pdf' => '/assets/pdfs/indef.pdf' ],
            [ 'name' => 'Inder',           'logo' => '/assets/images/brands/inder.png',      'pdf' => '/assets/pdfs/inder.pdf' ],
            [ 'name' => 'Jai Modula',      'logo' => '/assets/images/brands/jai-modula.png', 'pdf' => null ],
            [ 'name' => 'Jai WudPro',      'logo' => '/assets/images/brands/jai-wudpro.png', 'pdf' => null ],
            [ 'name' => 'Kirloskar',       'logo' => '/assets/images/brands/kirloskar.png',  'pdf' => '/assets/pdfs/kirloskar.pdf' ],
            [ 'name' => 'KisanKraft',      'logo' => '/assets/images/brands/kisankraft.png', 'pdf' => '/assets/pdfs/kisankraft.pdf' ],
            ];
            ?>

            <section id="brands" class="py-10 md:py-14 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Title -->
                <h3 class="text-center text-red-600 font-semibold text-xl sm:text-2xl md:text-3xl">
                Click on the Brand Logo to view detail PDF Catalogue
                </h3>

                <!-- Grid -->
                <div class="mt-8 md:mt-10 grid gap-x-10 gap-y-10
                            grid-cols-2 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-5 place-items-center">
                <?php foreach ($brands as $b): ?>
                    <?php
                    $name = htmlspecialchars($b['name']);
                    $logo = htmlspecialchars($b['logo']);
                    $pdf  = $b['pdf'] ? htmlspecialchars($b['pdf']) : null;
                    ?>

                    <?php if ($pdf): ?>
                    <!-- Clickable (opens PDF in new tab) -->
                    <a href="<?= $pdf ?>" target="_blank" rel="noopener"
                        class="group block p-2 sm:p-3 md:p-4 rounded transition-transform duration-200
                                hover:scale-[1.04] focus:outline-none focus-visible:ring focus-visible:ring-red-500/40"
                        title="View PDF: <?= $name ?>">
                        <img src="<?= $logo ?>" alt="<?= $name ?> logo"
                            class="h-14 sm:h-16 md:h-20 lg:h-24 w-auto object-contain">
                    </a>
                    <?php else: ?>
                    <!-- Not clickable -->
                    <div class="block p-2 sm:p-3 md:p-4 rounded cursor-default" title="<?= $name ?>">
                        <img src="<?= $logo ?>" alt="<?= $name ?> logo"
                            class="h-14 sm:h-16 md:h-20 lg:h-24 w-auto object-contain opacity-100">
                    </div>
                    <?php endif; ?>
                <?php endforeach; ?>
                </div>
            </div>
            </section>

    </main>

<?php include("footer.php"); ?>