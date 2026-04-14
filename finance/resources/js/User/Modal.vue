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
    background: rgba(5, 8, 15, 0.8);
    backdrop-filter: blur(8px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
  }
  .modal-content {
    background: rgba(15, 23, 41, 0.9);
    backdrop-filter: blur(24px);
    padding: 32px;
    border-radius: 16px;
    min-width: 400px;
    border: 1px solid rgba(255, 255, 255, 0.1);
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
  }
  .modal-close {
    box-sizing: border-box;
    border: none;
    width: 100%;
    height: 44px;
    margin-top: 24px;
    background: linear-gradient(to right, rgba(0, 212, 255, 1) 0%, rgba(190, 94, 237, 1) 100%);
    border-radius: 10px;
    box-shadow: 0 0 20px rgba(6, 182, 212, 0.3);
    transition: all 0.1s ease;
    font-size: 16px;
    font-family: 'Inter', sans-serif;
    font-weight: bold;
    color: white;
    cursor: pointer;
  }
  .modal-close:active {
    box-shadow: 0 0 10px rgba(6, 182, 212, 0.3);
    transform: translateY(3px);
  }
  .modal-close:hover {
    box-shadow: 0 0 20px rgba(6, 182, 212, 0.5);
  }
  :deep(.mb-3) {
    margin-bottom: 16px;
  }
  :deep(.form-control) {
    font-family: 'JetBrains Mono', monospace;
    font-size: 14px;
    border-radius: 10px;
    background: rgba(22, 33, 60, 0.5);
    border: 1px solid rgba(255, 255, 255, 0.1);
    outline: none;
    padding: 10px 13px;
    letter-spacing: 1.12px;
    box-sizing: border-box;
    width: 100%;
    color: white;
  }
  :deep(.form-control::placeholder) {
    color: rgba(148, 163, 184, 0.5);
  }
  :deep(select.form-control) {
    cursor: pointer;
  }
  :deep(select.form-control option) {
    background: rgba(15, 23, 41, 0.95);
    color: white;
  }
  :deep(.btn-primary) {
    box-sizing: border-box;
    border: none;
    width: 100%;
    height: 44px;
    background: linear-gradient(to right, rgba(0, 212, 255, 1) 0%, rgba(190, 94, 237, 1) 100%);
    border-radius: 10px;
    box-shadow: 0 0 20px rgba(6, 182, 212, 0.3);
    transition: all 0.1s ease;
    font-size: 16px;
    font-family: 'Inter', sans-serif;
    font-weight: bold;
    color: white;
    cursor: pointer;
  }
  :deep(.btn-primary:active) {
    box-shadow: 0 0 10px rgba(6, 182, 212, 0.3);
    transform: translateY(3px);
  }
  :deep(.btn-primary:hover) {
    box-shadow: 0 0 20px rgba(6, 182, 212, 0.5);
  }
  :deep(small) {
    display: block;
    margin-top: 4px;
    font-size: 12px;
    font-family: 'Inter', sans-serif;
    color: #94A3B8;
  }
</style>