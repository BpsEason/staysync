<template>
  <div class="p-6 bg-white rounded-lg shadow-xl">
    <h2 class="text-3xl font-bold mb-6 text-gray-900">{{ $t('iot.dashboard_title') }}</h2>

    <div v-if="loading" class="text-center text-gray-600">{{ $t('common.loading') }}...</div>
    <div v-else-if="error" class="text-center text-red-600">{{ $t('message.error') }}: {{ error }}</div>
    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
      <div v-for="device in devices" :key="device.id" class="bg-gray-50 rounded-lg shadow-md overflow-hidden transform transition-transform duration-300 hover:scale-103 hover:shadow-xl">
        <div class="p-5">
          <h3 class="text-xl font-semibold mb-2 text-gray-900">{{ device.name }} ({{ device.type }})</h3>
          <p class="text-gray-600 text-sm mb-2">{{ $t('iot.device_id') }}: {{ device.device_id }}</p>
          <p class="text-gray-600 text-sm mb-2">房源 ID: {{ device.property_id }}</p>
          <div class="flex justify-between items-center mb-4">
            <span :class="getStatusClass(device.status)" class="px-3 py-1 rounded-full text-xs font-semibold">{{ device.status }}</span>
            <span class="text-sm text-gray-500">{{ $t('iot.last_reading') }}: {{ device.last_reading ? JSON.stringify(device.last_reading) : 'N/A' }}</span>
          </div>

          <div class="mt-4 border-t border-gray-200 pt-4">
            <h4 class="text-lg font-semibold mb-3">{{ $t('iot.control') }}</h4>
            <div class="flex flex-wrap gap-2">
              <button v-if="device.type === 'light'" @click="sendControlCommand(device.device_id, 'turn_on')" class="bg-green-500 hover:bg-green-600 text-white font-bold py-1 px-3 rounded-md text-sm">{{ $t('iot.turn_on') }}</button>
              <button v-if="device.type === 'light'" @click="sendControlCommand(device.device_id, 'turn_off')" class="bg-red-500 hover:bg-red-600 text-white font-bold py-1 px-3 rounded-md text-sm">{{ $t('iot.turn_off') }}</button>
              <button v-if="device.type === 'lock'" @click="sendControlCommand(device.device_id, 'lock')" class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-1 px-3 rounded-md text-sm">{{ $t('iot.lock') }}</button>
              <button v-if="device.type === 'lock'" @click="sendControlCommand(device.device_id, 'unlock')" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-1 px-3 rounded-md text-sm">{{ $t('iot.unlock') }}</button>
              
              <div v-if="device.type === 'light'" class="flex items-center space-x-2">
                <input type="range" min="0" max="100" v-model="brightness[device.device_id]" @change="sendControlCommand(device.device_id, 'set_brightness', { brightness: parseInt(brightness[device.device_id]) })" class="w-24">
                <span class="text-sm text-gray-700">{{ brightness[device.device_id] || 0 }}%</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- WebSocket Status -->
    <div class="mt-8 p-4 bg-blue-50 border border-blue-200 rounded-lg shadow-inner">
      <h3 class="text-xl font-bold mb-3 text-blue-800">實時 IoT 設備更新 (WebSocket)</h3>
      <div v-for="(msg, index) in wsMessages" :key="index" class="text-sm text-blue-700 mb-1">
        {{ msg }}
      </div>
      <p v-if="wsMessages.length === 0" class="text-sm text-gray-500">等待實時設備更新...</p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import axios from 'axios';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const devices = ref([]);
const loading = ref(true);
const error = ref(null);
const brightness = ref({}); // Store brightness for each light device

const wsMessages = ref([]);
let websocket = null;

const API_BASE_URL = import.meta.env.VITE_API_BASE_URL;       // Laravel API Gateway
const FASTAPI_BASE_URL = import.meta.env.VITE_FASTAPI_BASE_URL; // FastAPI Base URL (e.g., http://localhost/fastapi)
const WS_BASE_URL = import.meta.env.VITE_WS_BASE_URL;         // WebSocket Base URL (e.g., ws://localhost/ws)

// Helper to get JWT token
const getToken = () => localStorage.getItem('token');

const fetchDevices = async () => {
  loading.value = true;
  error.value = null;
  try {
    const response = await axios.get(`${API_BASE_URL}/iot-devices`, {
      headers: { Authorization: `Bearer ${getToken()}` }
    });
    devices.value = response.data;
    // Initialize brightness for light devices
    devices.value.forEach(device => {
      if (device.type === 'light' && device.last_reading && typeof device.last_reading === 'object' && device.last_reading.brightness !== undefined) {
        brightness.value[device.device_id] = device.last_reading.brightness;
      }
    });
  } catch (err) {
    console.error('Error fetching IoT devices:', err);
    error.value = err.response?.data?.message || err.message;
  } finally {
    loading.value = false;
  }
};

const sendControlCommand = async (deviceId, command, value = {}) => {
  try {
    const response = await axios.post(`${FASTAPI_BASE_URL}/iot/control`, {
      device_id: deviceId,
      command: command,
      value: value
    });
    console.log('Command sent:', response.data);
    alert(t('message.success') + ': ' + response.data.message);
    // In a real app, the status update will come via WebSocket
    // For now, we can manually refetch or update a dummy status
    // fetchDevices(); // Consider if this should be triggered by WebSocket instead
  } catch (err) {
    console.error('Error sending control command:', err);
    alert(t('message.error') + ': ' + (err.response?.data?.detail || err.message));
  }
};

const getStatusClass = (status) => {
  switch (status) {
    case 'on':
    case 'connected':
    case 'unlocked':
      return 'bg-green-200 text-green-800';
    case 'off':
    case 'disconnected':
    case 'locked':
      return 'bg-red-200 text-red-800';
    default:
      return 'bg-gray-200 text-gray-800';
  }
};

const setupWebSocket = () => {
  websocket = new WebSocket(`${WS_BASE_URL}/iot/ws/status`); // Correct WebSocket endpoint

  websocket.onopen = () => {
    console.log("WebSocket connection established for IoT updates.");
    wsMessages.value.push("WebSocket 連接已建立。");
  };

  websocket.onmessage = (event) => {
    console.log("Received WebSocket message:", event.data);
    let updateData;
    try {
      updateData = JSON.parse(event.data);
    } catch (e) {
      console.warn("Failed to parse WebSocket message as JSON:", event.data, e);
      wsMessages.value.push(`原始訊息: ${event.data}`);
      return;
    }
    
    // Only process 'iot_status_update' events
    if (updateData.type === 'iot_status_update') {
      wsMessages.value.push(`設備 ${updateData.device_id} 狀態更新: ${updateData.status}`);
      
      const deviceId = updateData.device_id;
      const newStatus = updateData.status; // This could be a string or JSON string

      // Find and update the device in the local reactive array
      const deviceIndex = devices.value.findIndex(d => d.device_id === deviceId);
      if (deviceIndex !== -1) {
        devices.value[deviceIndex].status = newStatus;
        try {
          const parsedStatus = JSON.parse(newStatus); // Try parsing if it's a JSON string
          devices.value[deviceIndex].last_reading = parsedStatus;
          if (devices.value[deviceIndex].type === 'light' && parsedStatus.brightness !== undefined) {
            brightness.value[deviceId] = parsedStatus.brightness;
          }
        } catch (e) {
          devices.value[deviceIndex].last_reading = { raw_status: newStatus }; // Store as raw string if not JSON
        }
      } else {
        // If device not in list, consider refetching all devices or adding it dynamically
        console.warn(`Device ${deviceId} not found in current list, consider refetching.`);
        // fetchDevices(); // Potentially too aggressive, use with caution
      }
    }
  };

  websocket.onclose = (event) => {
    console.log("WebSocket connection closed:", event);
    wsMessages.value.push("WebSocket 連接已關閉。嘗試重新連接...");
    setTimeout(setupWebSocket, 3000); // Attempt to reconnect after 3 seconds
  };

  websocket.onerror = (err) => {
    console.error("WebSocket error:", err);
    wsMessages.value.push("WebSocket 錯誤發生。");
  };
};


onMounted(() => {
  fetchDevices();
  setupWebSocket();
});

onUnmounted(() => {
  if (websocket) {
    websocket.close();
  }
});
</script>

<style scoped>
/* Scoped styles if needed, Tailwind handles most. */
</style>
