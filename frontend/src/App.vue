<template>
  <div id="app" class="font-sans antialiased text-gray-800">
    <header class="bg-gradient-to-r from-blue-600 to-purple-600 shadow-lg p-4">
      <div class="container mx-auto flex justify-between items-center">
        <RouterLink to="/" class="text-white text-3xl font-extrabold tracking-wide">
          StaySync
        </RouterLink>
        <nav class="space-x-6">
          <RouterLink to="/" class="text-white hover:text-blue-200 transition duration-300 ease-in-out text-lg font-medium">{{ $t('home') }}</RouterLink>
          <RouterLink to="/properties" class="text-white hover:text-blue-200 transition duration-300 ease-in-out text-lg font-medium">{{ $t('properties') }}</RouterLink>
          <RouterLink to="/iot-devices" class="text-white hover:text-blue-200 transition duration-300 ease-in-out text-lg font-medium">{{ $t('iot_devices') }}</RouterLink>
          <RouterLink to="/culture" class="text-white hover:text-blue-200 transition duration-300 ease-in-out text-lg font-medium">{{ $t('culture.title') }}</RouterLink>
          <!-- More router links -->
          <!-- <RouterLink to="/bookings" class="text-white hover:text-blue-200 transition duration-300 ease-in-out text-lg font-medium">{{ $t('bookings') }}</RouterLink> -->
          <!-- <RouterLink to="/ai-chatbot" class="text-white hover:text-blue-200 transition duration-300 ease-in-out text-lg font-medium">{{ $t('ai_chatbot') }}</RouterLink> -->
          <!-- <RouterLink to="/dynamic-pricing" class="text-white hover:text-blue-200 transition duration-300 ease-in-out text-lg font-medium">{{ $t('dynamic_pricing') }}</RouterLink> -->
          <!-- <RouterLink to="/blockchain-fundraising" class="text-white hover:text-blue-200 transition duration-300 ease-in-out text-lg font-medium">{{ $t('blockchain_fundraising') }}</RouterLink> -->
          <!-- <RouterLink to="/design-studio" class="text-white hover:text-blue-200 transition duration-300 ease-in-out text-lg font-medium">{{ $t('design_studio') }}</RouterLink> -->
        </nav>
      </div>
    </header>
    <main class="container mx-auto p-6 min-h-screen">
      <RouterView />
    </main>
    <footer class="bg-gray-800 text-white p-4 mt-8 text-center">
      <p>&copy; 2024 StaySync. All rights reserved.</p>
    </footer>
  </div>
</template>

<script setup>
import { RouterLink, RouterView } from 'vue-router'
import { useI18n } from 'vue-i18n';
const { t } = useI18n();
</script>

<style scoped>
/* Add any global styles or scoped overrides here */
</style>
