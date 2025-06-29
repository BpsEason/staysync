<template>
  <div class="culture-content-container p-6 bg-white rounded-lg shadow-md">
    <div class="flex justify-between items-center mb-6">
      <h2 class="text-3xl font-bold text-gray-800">{{ $t('culture.title') }}</h2>
      <select v-model="selectedLanguage" @change="fetchContent" class="form-select border border-gray-300 rounded-md py-2 px-4">
        <option value="zh_TW">繁體中文</option>
        <option value="en">English</option>
        <option value="ja">日本語</option>
      </select>
    </div>
    
    <div v-if="loading" class="flex items-center justify-center p-10">
      <svg class="animate-spin h-8 w-8 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
      </svg>
      <span class="ml-3 text-lg text-gray-600">{{ $t('common.loading') }}...</span>
    </div>

    <div v-else-if="error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
      <strong class="font-bold">{{ $t('common.error') }}!</strong>
      <span class="block sm:inline ml-2">{{ error }}</span>
    </div>

    <div v-else class="grid md:grid-cols-2 gap-8">
      <article v-for="item in contents" :key="item.id" class="culture-item bg-gray-50 p-6 rounded-lg shadow-inner hover:shadow-lg transition-shadow duration-300">
        <h3 class="text-xl font-semibold text-indigo-700 mb-2">{{ item.title }}</h3>
        <p class="text-gray-600 leading-relaxed">{{ item.content }}</p>
        <div class="mt-4 text-sm text-gray-500">
          <span class="mr-4">#{{ item.category }}</span>
          <span><i class="fas fa-calendar-alt"></i> {{ new Date(item.created_at).toLocaleDateString() }}</span>
        </div>
      </article>
      <div v-if="contents.length === 0" class="col-span-2 text-center text-gray-500 py-10">
        <p>{{ $t('culture.no_content') }}</p>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, watch } from 'vue';
import axios from 'axios';
import { useI18n } from 'vue-i18n'; // Assuming you have vue-i18n configured

export default {
  name: 'CultureContent',
  setup() {
    const { locale, t } = useI18n();
    const contents = ref([]);
    const loading = ref(true);
    const error = ref(null);
    const selectedLanguage = ref(locale.value || 'zh_TW'); // Default to current locale or zh_TW

    const API_BASE_URL = import.meta.env.VITE_API_BASE_URL;

    const fetchContent = async () => {
      loading.value = true;
      error.value = null;
      try {
        const response = await axios.get(`${API_BASE_URL}/culture/contents?lang=${selectedLanguage.value}`, {
          headers: {
            // 確保 API 請求包含 X-Tenant-ID 頭部，從本地儲存或上下文獲取
            'X-Tenant-ID': localStorage.getItem('tenant_id'), 
            'Authorization': `Bearer ${localStorage.getItem('token')}` // 確保 JWT 令牌已傳遞
          }
        });
        contents.value = response.data;
      } catch (err) {
        error.value = t('culture.fetch_error');
        console.error('Failed to fetch cultural content:', err);
      } finally {
        loading.value = false;
      }
    };
    
    // Watch for changes in the selected language and refetch content
    watch(selectedLanguage, (newLang) => {
      locale.value = newLang; // Update global i18n locale
      fetchContent();
    });

    onMounted(() => {
      fetchContent();
    });

    return {
      contents,
      loading,
      error,
      selectedLanguage,
      t,
    };
  },
};
</script>

<style scoped>
/* Add some basic styling for the component */
.culture-content-container {
  max-width: 1200px;
  margin: 0 auto;
}
</style>

<i18n>
{
  "zh_TW": {
    "culture": {
      "title": "在地文化與體驗",
      "fetch_error": "無法載入文化內容，請稍後再試。",
      "no_content": "此語言目前沒有可用的文化內容。"
    },
    "common": {
      "loading": "載入中",
      "error": "錯誤"
    }
  },
  "en": {
    "culture": {
      "title": "Local Culture & Experiences",
      "fetch_error": "Failed to load cultural content. Please try again later.",
      "no_content": "No cultural content available in this language yet."
    },
    "common": {
      "loading": "Loading",
      "error": "Error"
    }
  },
  "ja": {
    "culture": {
      "title": "地元文化と体験",
      "fetch_error": "文化コンテンツの読み込みに失敗しました。後でもう一度お試しください。",
      "no_content": "この言語ではまだ利用可能な文化コンテンツはありません。"
    },
    "common": {
      "loading": "読み込み中",
      "error": "エラー"
    }
  }
}
</i18n>
