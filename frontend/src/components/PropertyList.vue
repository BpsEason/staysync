<template>
  <div class="p-6 bg-white rounded-lg shadow-xl">
    <h2 class="text-3xl font-bold mb-6 text-gray-900">{{ $t('property.list_title') }}</h2>

    <button @click="showAddModal = true" class="mb-6 bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg shadow-md transition duration-300 ease-in-out transform hover:scale-105">
      {{ $t('property.add_new') }}
    </button>

    <div v-if="loading" class="text-center text-gray-600">{{ $t('common.loading') }}...</div>
    <div v-else-if="error" class="text-center text-red-600">{{ $t('message.error') }}: {{ error }}</div>
    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
      <div v-for="property in properties" :key="property.id" class="bg-gray-50 rounded-lg shadow-md overflow-hidden transform transition-transform duration-300 hover:scale-103 hover:shadow-xl">
        <img :src="property.images && property.images.length > 0 ? property.images[0] : 'https://placehold.co/400x250/E0E7FF/000000?text=No+Image'" alt="Property Image" class="w-full h-48 object-cover">
        <div class="p-5">
          <h3 class="text-xl font-semibold mb-2 text-gray-900">{{ property.name }}</h3>
          <p class="text-gray-600 text-sm mb-3">{{ property.address }}</p>
          <div class="flex justify-between items-center mb-3">
            <span class="text-2xl font-bold text-blue-700">${{ property.base_price.toFixed(2) }}</span>
            <span :class="getStatusClass(property.status)" class="px-3 py-1 rounded-full text-xs font-semibold">{{ getStatusTranslated(property.status) }}</span>
          </div>
          <p class="text-gray-700 text-sm mb-4">{{ property.description }}</p>
          <div class="flex justify-end space-x-2">
            <button @click="editProperty(property)" class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-1 px-3 rounded-md text-sm">{{ $t('form.edit') }}</button>
            <button @click="deleteProperty(property.id)" class="bg-red-500 hover:bg-red-600 text-white font-bold py-1 px-3 rounded-md text-sm">{{ $t('form.delete') }}</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Add/Edit Property Modal -->
    <div v-if="showAddModal || showEditModal" class="fixed inset-0 bg-gray-600 bg-opacity-75 flex items-center justify-center p-4 z-50">
      <div class="bg-white rounded-lg shadow-2xl p-8 w-full max-w-lg">
        <h3 class="text-2xl font-bold mb-6 text-gray-900">{{ editingProperty ? $t('property.edit_property') : $t('property.add_new') }}</h3>
        <form @submit.prevent="editingProperty ? updateProperty() : createProperty()" class="space-y-4">
          <div>
            <label for="name" class="block text-sm font-medium text-gray-700">{{ $t('property.name') }}</label>
            <input type="text" id="name" v-model="form.name" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
          </div>
          <div>
            <label for="description" class="block text-sm font-medium text-gray-700">{{ $t('property.description') }}</label>
            <textarea id="description" v-model="form.description" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"></textarea>
          </div>
          <div>
            <label for="address" class="block text-sm font-medium text-gray-700">{{ $t('property.address') }}</label>
            <input type="text" id="address" v-model="form.address" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
          </div>
          <div>
            <label for="base_price" class="block text-sm font-medium text-gray-700">{{ $t('property.price') }}</label>
            <input type="number" id="base_price" v-model.number="form.base_price" required min="0" step="0.01" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
          </div>
          <div>
            <label for="status" class="block text-sm font-medium text-gray-700">{{ $t('property.status') }}</label>
            <select id="status" v-model="form.status" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
              <option value="available">{{ $t('property.available') }}</option>
              <option value="unavailable">{{ $t('property.unavailable') }}</option>
              <option value="draft">{{ $t('property.draft') }}</option>
            </select>
          </div>
          <div>
            <label for="amenities" class="block text-sm font-medium text-gray-700">{{ $t('property.amenities') }} (Comma separated)</label>
            <input type="text" id="amenities" v-model="form.amenities_input" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
          </div>
          <div>
            <label for="images" class="block text-sm font-medium text-gray-700">{{ $t('property.images') }} (Comma separated URLs)</label>
            <input type="text" id="images" v-model="form.images_input" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
          </div>
          <div class="flex justify-end space-x-3 mt-6">
            <button type="button" @click="closeModal" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded-lg transition duration-300 ease-in-out">{{ $t('form.cancel') }}</button>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition duration-300 ease-in-out">{{ $t('form.save') }}</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const properties = ref([]);
const loading = ref(true);
const error = ref(null);
const showAddModal = ref(false);
const showEditModal = ref(false);
const editingProperty = ref(null);

const form = ref({
  name: '',
  description: '',
  address: '',
  base_price: 0,
  status: 'draft',
  amenities_input: '', // For comma-separated input
  images_input: '',    // For comma-separated URLs input
  latitude: null,
  longitude: null,
});

const API_BASE_URL = import.meta.env.VITE_API_BASE_URL; // Laravel API Gateway

// Helper to get JWT token
const getToken = () => localStorage.getItem('token');

const fetchProperties = async () => {
  loading.value = true;
  error.value = null;
  try {
    const response = await axios.get(`${API_BASE_URL}/properties`, {
      headers: { Authorization: `Bearer ${getToken()}` }
    });
    properties.value = response.data;
  } catch (err) {
    console.error('Error fetching properties:', err);
    error.value = err.response?.data?.message || err.message;
  } finally {
    loading.value = false;
  }
};

const createProperty = async () => {
  try {
    const payload = {
      ...form.value,
      amenities: form.value.amenities_input ? form.value.amenities_input.split(',').map(s => s.trim()) : [],
      images: form.value.images_input ? form.value.images_input.split(',').map(s => s.trim()) : [],
    };
    const response = await axios.post(`${API_BASE_URL}/properties`, payload, {
      headers: { Authorization: `Bearer ${getToken()}` }
    });
    properties.value.push(response.data);
    closeModal();
    alert(t('message.success') + ' ' + t('property.add_new') + '!');
  } catch (err) {
    console.error('Error creating property:', err);
    alert(t('message.error') + ' ' + (err.response?.data?.message || err.message));
  }
};

const editProperty = (property) => {
  editingProperty.value = property;
  form.value = {
    ...property,
    amenities_input: property.amenities ? property.amenities.join(', ') : '',
    images_input: property.images ? property.images.join(', ') : '',
  };
  showEditModal.value = true;
};

const updateProperty = async () => {
  try {
    const payload = {
      ...form.value,
      amenities: form.value.amenities_input ? form.value.amenities_input.split(',').map(s => s.trim()) : [],
      images: form.value.images_input ? form.value.images_input.split(',').map(s => s.trim()) : [],
    };
    const response = await axios.put(`${API_BASE_URL}/properties/${editingProperty.value.id}`, payload, {
      headers: { Authorization: `Bearer ${getToken()}` }
    });
    // Update the item in the local array
    const index = properties.value.findIndex(p => p.id === editingProperty.value.id);
    if (index !== -1) {
      properties.value[index] = response.data;
    }
    closeModal();
    alert(t('message.success') + ' ' + t('form.save') + '!');
  } catch (err) {
    console.error('Error updating property:', err);
    alert(t('message.error') + ' ' + (err.response?.data?.message || err.message));
  }
};

const deleteProperty = async (id) => {
  if (confirm('您確定要刪除此房源嗎？')) {
    try {
      await axios.delete(`${API_BASE_URL}/properties/${id}`, {
        headers: { Authorization: `Bearer ${getToken()}` }
      });
      properties.value = properties.value.filter(p => p.id !== id);
      alert(t('message.success') + ' ' + t('form.delete') + '!');
    } catch (err) {
      console.error('Error deleting property:', err);
      alert(t('message.error') + ' ' + (err.response?.data?.message || err.message));
    }
  }
};

const closeModal = () => {
  showAddModal.value = false;
  showEditModal.value = false;
  editingProperty.value = null;
  resetForm();
};

const resetForm = () => {
  form.value = {
    name: '',
    description: '',
    address: '',
    base_price: 0,
    status: 'draft',
    amenities_input: '',
    images_input: '',
    latitude: null,
    longitude: null,
  };
};

const getStatusClass = (status) => {
  switch (status) {
    case 'available': return 'bg-green-200 text-green-800';
    case 'unavailable': return 'bg-red-200 text-red-800';
    case 'draft': return 'bg-yellow-200 text-yellow-800';
    default: return 'bg-gray-200 text-gray-800';
  }
};

const getStatusTranslated = (status) => {
  return t(`property.${status}`);
};

onMounted(fetchProperties);
</script>

<style scoped>
/* Scoped styles if needed, Tailwind handles most. */
</style>
