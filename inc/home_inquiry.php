<!-- Quick Inquiry -->
<section id="home_inquiry" class="relative overflow-hidden">
  <!-- Red base -->
  <div class="absolute inset-0 bg-[#e21e26] -z-10"></div>
  <!-- Grid pattern overlay (repeat) -->
  <div class="absolute inset-0 opacity-40 -z-10"
       style="background-image:url('assets/images/home_bg2.jpg'); background-repeat:repeat; background-size:auto;"></div>

  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 md:py-14">
    <div class="grid md:grid-cols-12 gap-8 md:gap-10 items-center">
      <!-- Left: person -->
      <div class="md:col-span-5 flex justify-center md:justify-start">
        <!-- Use your cutout/PNG here -->
        <img src="assets/images/8F5YX8CKJS-20180613-150311.png" alt="Support professional"
             class="w-64 sm:w-72 md:w-[22rem] h-auto object-contain drop-shadow-xl">
      </div>

      <!-- Right: form -->
      <div class="md:col-span-7 text-white">
        <h2 class="font-serif text-3xl sm:text-4xl md:text-5xl font-semibold mb-6">
          Quick Inquiry
        </h2>

        <form action="/submit_inquiry.php" method="post" class="space-y-4">
          <!-- Row 1 -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <label class="sr-only" for="first_name">First Name</label>
            <input id="first_name" name="first_name" type="text" placeholder="First Name"
                   class="w-full px-4 py-3 bg-white text-gray-900 border-2 border-gray-400  focus:border-black/60 focus:ring-2 focus:ring-black/30 outline-none" />

            <label class="sr-only" for="last_name">Last Name</label>
            <input id="last_name" name="last_name" type="text" placeholder="Last Name"
                   class="w-full px-4 py-3 bg-white text-gray-900 border-2 border-gray-400  focus:border-black/60 focus:ring-2 focus:ring-black/30 outline-none" />
          </div>

          <!-- Row 2 -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <label class="sr-only" for="email">Email Id</label>
            <input id="email" name="email" type="email" placeholder="Email Id"
                   class="w-full px-4 py-3 bg-white text-gray-900 border-2 border-gray-400  focus:border-black/60 focus:ring-2 focus:ring-black/30 outline-none" />

            <label class="sr-only" for="phone">Phone Number</label>
            <input id="phone" name="phone" type="tel" placeholder="Phone Number"
                   class="w-full px-4 py-3 bg-white text-gray-900 border-2 border-gray-400  focus:border-black/60 focus:ring-2 focus:ring-black/30 outline-none" />
          </div>

          <!-- Message -->
          <label class="sr-only" for="message">Message</label>
          <textarea id="message" name="message" rows="4" placeholder="Message"
                    class="w-full px-4 py-3 bg-white text-gray-900 border-2 border-gray-400  focus:border-black/60 focus:ring-2 focus:ring-black/30 outline-none"></textarea>

          <!-- Submit -->
          <div class="pt-2 flex justify-start">
            <button type="submit"
                    class="px-8 py-3 bg-black text-white font-semibold tracking-wide uppercase hover:bg-neutral-900 transition">
              Submit
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</section>
