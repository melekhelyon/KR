<template>
  <div v-if="visible" class="modal-overlay" @click.self="handleOverlayClick">
    <div class="modal-content">
      <slot></slot>
      <button class="modal-close" @click="close">Закрыть</button>
    </div>
  </div>
</template>

<script>
export default {
  name: 'BaseModal',

  props: {
    visible: {
      type: Boolean,
      required: true
    }
  },
  
  methods: {
    close() {
      this.$emit('close');
    },
    handleOverlayClick() {
      this.close();
    }
  }
}
</script>

<style scoped>
    .modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(4px);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1000;
    }
    .modal-content {
        background: white;
        padding: 32px;
        border-radius: 16px;
        min-width: 400px;
        border: 1px solid #E2E8F0;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    }
    .modal-close {
        box-sizing: border-box;
        border: none;
        width: 100%;
        height: 44px;
        margin-top: 24px;
        background: linear-gradient(to right, rgba(0, 212, 255, 1) 0%, rgba(190, 94, 237, 1) 100%);
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(0, 212, 255, 0.2);
        transition: all 0.1s ease;
        font-size: 16px;
        font-family: 'Inter', sans-serif;
        font-weight: bold;
        color: white;
        cursor: pointer;
    }
    .modal-close:active {
        box-shadow: 0 2px 10px rgba(0, 212, 255, 0.2);
        transform: translateY(3px);
    }
    .modal-close:hover {
        box-shadow: 0 6px 20px rgba(0, 212, 255, 0.3);
    }
    :deep(.mb-3) {
        margin-bottom: 16px;
    }
    :deep(.form-control) {
        font-family: 'JetBrains Mono', monospace;
        font-size: 14px;
        border-radius: 10px;
        background: #F1F5F9;
        border: 1px solid #E2E8F0;
        outline: none;
        padding: 10px 13px;
        letter-spacing: 1.12px;
        box-sizing: border-box;
        width: 100%;
        color: #0F172A;
    }
    :deep(.form-control::placeholder) {
        color: #94A3B8;
    }
    :deep(select.form-control) {
        cursor: pointer;
    }
    :deep(select.form-control option) {
        background: white;
        color: #0F172A;
    }
    :deep(.btn-primary) {
        box-sizing: border-box;
        border: none;
        width: 100%;
        height: 44px;
        background: linear-gradient(to right, rgba(0, 212, 255, 1) 0%, rgba(190, 94, 237, 1) 100%);
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(0, 212, 255, 0.2);
        transition: all 0.1s ease;
        font-size: 16px;
        font-family: 'Inter', sans-serif;
        font-weight: bold;
        color: white;
        cursor: pointer;
    }
    :deep(.btn-primary:active) {
        box-shadow: 0 2px 10px rgba(0, 212, 255, 0.2);
        transform: translateY(3px);
    }
    :deep(.btn-primary:hover) {
        box-shadow: 0 6px 20px rgba(0, 212, 255, 0.3);
    }
    :deep(small) {
        display: block;
        margin-top: 4px;
        font-size: 12px;
        font-family: 'Inter', sans-serif;
        color: #64748B;
    }
</style>