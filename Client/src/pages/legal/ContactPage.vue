<template>
  <div class="min-h-screen bg-white">
    <nav class="fixed top-0 left-0 right-0 z-50 glass-effect border-b border-gray-200/50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6">
        <div class="flex justify-between items-center h-16 sm:h-18 md:h-20">
          <router-link to="/" class="flex items-center gap-2 sm:gap-3 flex-shrink-0">
            <img 
              src="/Noras Logo.jpg" 
              alt="Noras"
              class="h-8 sm:h-9 md:h-10 w-auto object-contain"
              loading="eager"
            />
            <span class="text-xl sm:text-2xl md:text-2xl font-bold text-gray-900 tracking-tight">Noras</span>
          </router-link>
        </div>
      </div>
    </nav>

    <div class="pt-24 sm:pt-28 md:pt-32 pb-20">
      <div class="max-w-4xl mx-auto px-6">
        <h1 class="text-5xl font-bold text-gray-900 mb-8">Contact Us</h1>
        
        <div class="prose prose-lg max-w-none">
          <p class="text-xl text-gray-600 mb-8">
            We'd love to hear from you. Get in touch with the Noras team.
          </p>

          <div class="bg-white rounded-2xl border border-gray-200 p-8 mb-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Send us a message</h2>
            
            <form class="space-y-6">
              <div>
                <label class="block text-sm font-semibold text-gray-900 mb-2">Your Name</label>
                <input type="text" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none" placeholder="John Doe" />
              </div>

              <div>
                <label class="block text-sm font-semibold text-gray-900 mb-2">Email Address</label>
                <input type="email" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none" placeholder="john@example.com" />
              </div>

              <div>
                <label class="block text-sm font-semibold text-gray-900 mb-2">Subject</label>
                <input type="text" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none" placeholder="How can we help?" />
              </div>

              <div>
                <label class="block text-sm font-semibold text-gray-900 mb-2">Message</label>
                <textarea rows="6" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none resize-none" placeholder="Your message..."></textarea>
              </div>

              <button type="submit" class="bg-blue-600 text-white px-8 py-3 rounded-xl hover:bg-yellow-400 hover:text-gray-900 transition-all font-semibold shadow-md hover:shadow-lg">
                Send Message
              </button>
            </form>
          </div>

          <div class="grid md:grid-cols-2 gap-6">
            <div class="bg-gray-50 rounded-2xl p-6 border border-gray-200">
              <h3 class="font-bold text-gray-900 mb-2">Email</h3>
              <p class="text-gray-600">support@noras.edu.et</p>
            </div>

            <div class="bg-gray-50 rounded-2xl p-6 border border-gray-200">
              <h3 class="font-bold text-gray-900 mb-2">Location</h3>
              <p class="text-gray-600">Wollo University, Ethiopia</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
</script>
