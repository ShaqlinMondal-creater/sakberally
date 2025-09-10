<?php include("header.php"); ?>
<?php $page= "Inquiry Form"; ?>

    <main class="mx-auto pt-[112px] md:pt-[112px]">
        <?php include("inc/breadcrumb.php"); ?>

        <!-- Quick Inquiry -->
        <section id="inquiry" class="relative overflow-hidden">
        <!-- Red base -->
        <div class="absolute inset-0 -z-10"></div>
        <!-- Grid pattern overlay (repeat) -->
        

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 md:py-14">
            <div class="grid md:grid-cols-12 gap-8 md:gap-10 items-center">
                <!-- Right: form -->
                <div class="md:col-span-7 text-white">
                    <h2 class="font-serif text-3xl sm:text-4xl md:text-5xl font-semibold mb-6">
                        Quick Inquiry
                    </h2>

                    <!-- Add an id so we can target it in JS -->
                    <form id="inquiryForms" class="space-y-4" enctype="multipart/form-data">
                        <!-- Hidden subject default -->
                        <input type="hidden" name="subject" value="inquery" />

                        <!-- Row 1 -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <label class="sr-only" for="first_names">First Name</label>
                        <input id="first_names" name="first_names" type="text" placeholder="First Name"
                                class="w-full px-4 py-3 bg-white text-gray-900 border-2 border-gray-400 focus:border-black/60 focus:ring-2 focus:ring-black/30 outline-none" required />

                        <label class="sr-only" for="last_names">Last Name</label>
                        <input id="last_names" name="last_names" type="text" placeholder="Last Name"
                                class="w-full px-4 py-3 bg-white text-gray-900 border-2 border-gray-400 focus:border-black/60 focus:ring-2 focus:ring-black/30 outline-none" />
                        </div>

                        <!-- Row 2 -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <label class="sr-only" for="emails">Email Id</label>
                        <input id="emails" name="emails" type="email" placeholder="Email Id"
                                class="w-full px-4 py-3 bg-white text-gray-900 border-2 border-gray-400 focus:border-black/60 focus:ring-2 focus:ring-black/30 outline-none" required />

                        <label class="sr-only" for="phones">Phone Number</label>
                        <input id="phones" name="phones" type="tel" placeholder="Phone Number"
                                class="w-full px-4 py-3 bg-white text-gray-900 border-2 border-gray-400 focus:border-black/60 focus:ring-2 focus:ring-black/30 outline-none" required />
                        </div>

                        <!-- Message -->
                        <label class="sr-only" for="messages">Message</label>
                        <textarea id="messages" name="messages" rows="4" placeholder="Message"
                                class="w-full px-4 py-3 bg-white text-gray-900 border-2 border-gray-400 focus:border-black/60 focus:ring-2 focus:ring-black/30 outline-none" required></textarea>

                        <!-- Attachment -->
                        <div>
                        <label for="attachments" class="block mb-1">Attachment (optional)</label>
                        <input id="attachments" name="attachments" type="file"
                                class="block w-full text-sm text-gray-900 bg-white border-2 border-gray-400 file:mr-4 file:py-2 file:px-4 file:border-0 file:bg-black file:text-white file:uppercase file:tracking-wide hover:file:bg-neutral-900" />
                        </div>

                        <!-- Submit -->
                        <div class="pt-2 flex justify-start">
                        <button id="submitBtns" type="submit"
                                class="px-8 py-3 bg-black text-white font-semibold tracking-wide uppercase hover:bg-neutral-900 transition">
                            Submit
                        </button>
                        </div>
                    </form>
                </div>

                 <!-- Left: person -->
                <div class="md:col-span-5 flex justify-center md:justify-start">
                    <!-- Use your cutout/PNG here -->
                    <img src="assets/images/J3P6WJP1CG-20180614-092654.jpg" alt="Support professional"
                        class="w-64 sm:w-72 md:w-[22rem] h-auto object-contain">
                </div>
            </div>
        </div>

        </section>

    </main>
<script>
  // If you're rendering base_url server-side, leave as-is; otherwise set manually:

  const formEl = document.getElementById('inquiryForms');
  const submitBtn = document.getElementById('submitBtns');

  formEl.addEventListener('submit', async (e) => {
    e.preventDefault();

    // Gather fields
    const first = (document.getElementById('first_names').value || '').trim();
    const last  = (document.getElementById('last_names').value || '').trim();
    const email = (document.getElementById('emais').value || '').trim();
    const phone = (document.getElementById('phones').value || '').trim();
    const msg   = (document.getElementById('messages').value || '').trim();
    const subject = 'inquery'; // default as required
    const fileInput = document.getElementById('attachments');

    // Build FormData as required by API
    const fd = new FormData();
    fd.append('name', [first, last].filter(Boolean).join(' ').trim()); // combine first + last
    fd.append('mobile', phone);
    fd.append('email', email);
    fd.append('subject', subject);
    fd.append('messege', msg); // API expects "messege" (spelling)
    if (fileInput.files && fileInput.files[0]) {
      fd.append('attachment', fileInput.files[0]); // API expects "attachment"
    }

    // UI state
    submitBtn.disabled = true;
    submitBtn.classList.add('opacity-60', 'cursor-not-allowed');

    try {
      const res = await fetch(`<?php echo BASE_URL; ?>/query/create.php`, {
        method: 'POST',
        body: fd
      });

      const data = await res.json();

      if (!res.ok || data.success !== true) {
        const errMsg = (data && data.message) ? data.message : 'Something went wrong.';
        throw new Error(errMsg);
      }

      // Success: show SweetAlert for 2 seconds, then refresh
      const userName = (data.data && data.data.name) ? data.data.name : (first || 'User');
      const userMsg  = (data.data && data.data.messege) ? data.data.messege : msg;

      await Swal.fire({
        icon: 'success',
        title: `Thanks for Response ${userName}`,
        html: `Your messege is: <b>${escapeHtml(userMsg)}</b>`,
        timer: 2000,
        showConfirmButton: false,
        timerProgressBar: true,
        didOpen: () => {
          const b = Swal.getHtmlContainer().querySelector('b');
          if (b) b.style.wordBreak = 'break-word';
        }
      });

      // Refresh page
      window.location.reload();

    } catch (err) {
      console.error(err);
      Swal.fire({
        icon: 'error',
        title: 'Submission Failed',
        text: err.message || 'Unable to submit your inquiry right now.'
      });
    } finally {
      submitBtn.disabled = false;
      submitBtn.classList.remove('opacity-60', 'cursor-not-allowed');
    }
  });

  // Basic HTML escaper to prevent HTML injection inside the popup
  function escapeHtml(str) {
    return String(str)
      .replaceAll('&', '&amp;')
      .replaceAll('<', '&lt;')
      .replaceAll('>', '&gt;')
      .replaceAll('"', '&quot;')
      .replaceAll("'", '&#39;');
  }
</script>
<?php include("footer.php"); ?>