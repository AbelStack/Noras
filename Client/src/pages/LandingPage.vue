<template>
  <div class="min-h-screen bg-white">
    <NavigationBar />
    <HeroSection />
    <FeaturesSection />
    <HowItWorksSection />
    <BenefitsSection />
    <CTASection />
    <FooterSection />
  </div>
</template>

<script setup>
import NavigationBar from '../modules/public/components/NavigationBar.vue'
import HeroSection from '../modules/public/components/HeroSection.vue'
import FeaturesSection from '../modules/public/components/FeaturesSection.vue'
import HowItWorksSection from '../modules/public/components/HowItWorksSection.vue'
import BenefitsSection from '../modules/public/components/BenefitsSection.vue'
import CTASection from '../modules/public/components/CTASection.vue'
import FooterSection from '../modules/public/components/FooterSection.vue'
</script>
