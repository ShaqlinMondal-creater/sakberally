<?php include("header.php"); ?>
<?php $page= "Anout Us"; ?>
<main class="mx-auto pt-[112px] md:pt-[112px]">
  <?php include("inc/breadcrumb.php"); ?>
     
    <!-- About Us -->
    <section id="about" class="bg-white">
      <!-- Content -->
      <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pt-10 md:pt-12">

        <!-- Person + text block -->
        <div class="grid md:grid-cols-12 gap-8 md:gap-10 items-start">
          <!-- Left: person -->
          <div class="md:col-span-4 flex md:block justify-center">
            <img
              src="/assets/images/about/person.png" 
              alt="Our representative"
              class="w-60 sm:w-72 md:w-[20rem] h-auto object-contain drop-shadow" />
          </div>

          <!-- Right: text -->
          <div class="md:col-span-8 text-gray-800 leading-7 md:leading-8 text-[15px] md:text-base">
            <p>
              <span class="font-bold text-[#e21e26]">S. AKBERALLY &amp; CO.</span> is a Partnership owned organization,
              which came into existence in the year <span class="font-bold">1987</span> and is highly acclaimed for
              meeting the expectations of customers. Our company has focused all its efforts towards providing assortment
              according to the upcoming desires of customers.
            </p>

            <p class="mt-4">
              Products that have been offered by us encompass
              <span class="font-semibold">Woodworking Machine</span>,
              <span class="font-semibold">Glass Polishing Machine</span>,
              <span class="font-semibold">Vertical Bandsaw Machine</span>,
              <span class="font-semibold">Car Washer Machine</span>,
              <span class="font-semibold">Cylinder Boring Machine</span>,
              <span class="font-semibold">Sheet Rolling Machine</span>,
              <span class="font-semibold">Hacksaw Machine</span>,
              <span class="font-semibold">Metal Cutting Bandsaw</span>,
              <span class="font-semibold">Welding Machine</span>,
              <span class="font-semibold">Lathe Machine</span>,
              <span class="font-semibold">Chain Mortiser</span>,
              <span class="font-semibold">Thickness Planer</span>,
              <span class="font-semibold">Concrete Vibrator</span>,
              <span class="font-semibold">Material Handling Equipment</span>
              and many more.
            </p>

            <!-- Right-side showroom image (floats on desktop, stacks on mobile) -->
            <img
              src="/assets/images/about/showroom-1.jpg"
              alt="Showroom"
              class="w-full md:w-[360px] rounded shadow md:float-right md:ml-6 md:mb-2 mt-5 md:mt-2" />

            <p class="mt-4 clear-both md:clear-none">
              All our products are designed perfectly by highly experienced and qualified personnel, who are expert in this
              domain and are aware of the upcoming customers’ demands. Branded components and other qualitative raw inputs
              have been used in the production of our whole gamut, which we procure from the reliable and certified vendors
              of industry. We have adopted advanced technology in order to cope up with the challenges of industry.
            </p>

            <p class="mt-4">
              <span class="font-bold text-[#e21e26]">Mr. Yakub Johar</span> is our honorable Partner, under whose astute
              guidance we have managed to establish our name in the list of leading firms of industry. With his sound
              business insight, administrative qualities, managerial skills, rich industrial experience and technical
              knowledge, our company has gained immense support and trust of the customers.
            </p>

            <p class="mt-4">
              We are a quality-owned firm, acknowledging only the supply of best and error-free products. Our company
              possesses a sophisticated quality testing department with the latest testing equipment to check product
              quality efficiently. We upgrade them on a regular basis to enhance performance and efficiency. To perform
              quality checks efficiently, we have selected a well-versed team of quality controllers.
            </p>
          </div>
        </div>

        <!-- Bottom gallery -->
        <div class="mt-10 grid grid-cols-1 sm:grid-cols-3 gap-6">
          <img src="/assets/images/about/showroom-2.jpg" alt="Gallery 1" class="w-full h-auto rounded shadow object-cover">
          <img src="/assets/images/about/showroom-3.jpg" alt="Gallery 2" class="w-full h-auto rounded shadow object-cover">
          <img src="/assets/images/about/showroom-4.jpg" alt="Gallery 3" class="w-full h-auto rounded shadow object-cover">
        </div>

        <!-- Bottom spacing -->
        <div class="h-8 md:h-12"></div>
      </div>
    </section>
</main>


<?php include("footer.php"); ?>
