import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'
import PropertyList from '../components/PropertyList.vue'
import IoTDeviceDashboard from '../components/IoTDeviceDashboard.vue'
import CultureContent from '../components/CultureContent.vue' # Import new component

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'home',
      component: HomeView
    },
    {
      path: '/properties',
      name: 'properties',
      component: PropertyList
    },
    {
      path: '/iot-devices',
      name: 'iot-devices',
      component: IoTDeviceDashboard
    },
    {
      path: '/culture', # New route for cultural content
      name: 'culture',
      component: CultureContent
    },
    // Add more StaySync related routes here
    // {
    //   path: '/bookings',
    //   name: 'bookings',
    //   component: () => import('../components/BookingManagement.vue')
    // },
    // {
    //   path: '/ai-chatbot',
    //   name: 'ai-chatbot',
    //   component: () => import('../components/AIChatbot.vue')
    // },
    // {
    //   path: '/dynamic-pricing',
    //   name: 'dynamic-pricing',
    //   component: () => import('../components/DynamicPricingReport.vue')
    // },
    // {
    //   path: '/blockchain-fundraising',
    //   name: 'blockchain-fundraising',
    //   component: () => import('../components/BlockchainFundraising.vue')
    // },
    // {
    //   path: '/design-studio',
    //   name: 'design-studio',
    //   component: () => import('../components/DesignStudio.vue')
    // }
  ]
})

export default router
