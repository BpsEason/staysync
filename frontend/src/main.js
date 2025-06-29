import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import { createPinia } from 'pinia'
import { createI18n } from 'vue-i18n'
import './assets/main.css' // Tailwind CSS import

// i18n messages
const messages = {
  en: {
    welcome: 'Welcome to StaySync!',
    home: 'Home',
    properties: 'Properties',
    bookings: 'Bookings',
    iot_devices: 'IoT Devices',
    ai_chatbot: 'AI Chatbot',
    dynamic_pricing: 'Dynamic Pricing',
    blockchain_fundraising: 'Blockchain Fundraising',
    design_studio: 'Design Studio',
    dashboard: 'Dashboard',
    culture: {
      title: 'Local Culture & Experiences',
      fetch_error: 'Failed to load cultural content. Please try again later.',
      no_content: 'No cultural content available in this language yet.'
    },
    property: {
      list_title: 'Property Listings',
      add_new: 'Add New Property',
      edit_property: 'Edit Property',
      name: 'Name',
      description: 'Description',
      address: 'Address',
      price: 'Base Price',
      status: 'Status',
      amenities: 'Amenities',
      images: 'Images',
      available: 'Available',
      unavailable: 'Unavailable',
      draft: 'Draft'
    },
    iot: {
      dashboard_title: 'IoT Device Dashboard',
      device_id: 'Device ID',
      name: 'Name',
      type: 'Type',
      status: 'Status',
      last_reading: 'Last Reading',
      control: 'Control',
      turn_on: 'Turn On',
      turn_off: 'Turn Off',
      lock: 'Lock',
      unlock: 'Unlock',
      set_brightness: 'Set Brightness',
      brightness: 'Brightness'
    },
    form: {
      submit: 'Submit',
      cancel: 'Cancel',
      save: 'Save',
      edit: 'Edit',
      delete: 'Delete'
    },
    message: {
      success: 'Success!',
      error: 'Error!'
    },
    common: {
      loading: 'Loading',
      error: 'Error'
    }
  },
  'zh-TW': {
    welcome: '歡迎來到 StaySync！',
    home: '首頁',
    properties: '房源管理',
    bookings: '預訂管理',
    iot_devices: 'IoT 設備',
    ai_chatbot: 'AI 客服',
    dynamic_pricing: '動態定價',
    blockchain_fundraising: '區塊鏈集資',
    design_studio: '設計工作室',
    dashboard: '儀表板',
    culture: {
      title: '在地文化與體驗',
      fetch_error: '無法載入文化內容，請稍後再試。',
      no_content: '此語言目前沒有可用的文化內容。'
    },
    property: {
      list_title: '房源列表',
      add_new: '新增房源',
      edit_property: '編輯房源',
      name: '名稱',
      description: '描述',
      address: '地址',
      price: '基礎價格',
      status: '狀態',
      amenities: '設施',
      images: '圖片',
      available: '可預訂',
      unavailable: '不可預訂',
      draft: '草稿'
    },
    iot: {
      dashboard_title: 'IoT 設備儀表板',
      device_id: '設備 ID',
      name: '名稱',
      type: '類型',
      status: '狀態',
      last_reading: '最新讀數',
      control: '控制',
      turn_on: '開啟',
      turn_off: '關閉',
      lock: '鎖定',
      unlock: '解鎖',
      set_brightness: '設定亮度',
      brightness: '亮度'
    },
    form: {
      submit: '提交',
      cancel: '取消',
      save: '儲存',
      edit: '編輯',
      delete: '刪除'
    },
    message: {
      success: '成功!',
      error: '錯誤!'
    },
    common: {
      loading: '載入中',
      error: '錯誤'
    }
  },
  'ja': {
    welcome: 'StaySyncへようこそ！',
    home: 'ホーム',
    properties: '物件管理',
    bookings: '予約',
    iot_devices: 'IoTデバイス',
    ai_chatbot: 'AIチャットボット',
    dynamic_pricing: '動的価格設定',
    blockchain_fundraising: 'ブロックチェーン資金調達', # Adjusted for clarity
    design_studio: 'デザインスタジオ',
    dashboard: 'ダッシュボード',
    culture: {
      title: '地域の文化と体験',
      fetch_error: '文化コンテンツの読み込みに失敗しました。後でもう一度お試しください。',
      no_content: 'この言語ではまだ利用可能な文化コンテンツはありません。'
    },
    property: {
      list_title: '物件リスト',
      add_new: '新しい物件を追加',
      edit_property: '物件を編集',
      name: '名前',
      description: '説明',
      address: '住所',
      price: '基本価格',
      status: 'ステータス',
      amenities: '設備',
      images: '画像',
      available: '利用可能',
      unavailable: '利用不可',
      draft: 'ドラフト'
    },
    iot: {
      dashboard_title: 'IoTデバイスダッシュボード',
      device_id: 'デバイスID',
      name: '名前',
      type: 'タイプ',
      status: 'ステータス',
      last_reading: '最終読み取り',
      control: '制御',
      turn_on: 'オン',
      turn_off: 'オフ',
      lock: 'ロック',
      unlock: 'ロック解除',
      set_brightness: '明るさ設定',
      brightness: '明るさ'
    },
    form: {
      submit: '送信',
      cancel: 'キャンセル',
      save: '保存',
      edit: '編集',
      delete: '削除'
    },
    message: {
      success: '成功！',
      error: 'エラー！'
    },
    common: {
      loading: '読み込み中',
      error: 'エラー'
    }
  }
}

const i18n = createI18n({
  legacy: false,
  locale: 'zh-TW', // Default locale
  fallbackLocale: 'en',
  messages
})

const app = createApp(App)
const pinia = createPinia()

app.use(router)
app.use(pinia)
app.use(i18n)

app.mount('#app')
