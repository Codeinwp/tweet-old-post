<template>
  <div
    class="modal"
    :class="{'active': isOpen}"
  >
    <div
      class="modal-overlay"
      @click="$emit('cancel-modal')"
    />
    <div class="modal-container">
      <div class="modal-header">
        <button
          class="btn btn-clear float-right"
          @click="$emit('cancel-modal')"
        />
        <div class="modal-title h5">
          <slot name="modal-title" />
        </div>
      </div>
      <div class="modal-body">
        <div class="content">
          <slot name="modal-content" />
        </div>
      </div>
  
      <slot name="modal-extra" />
      
      <div class="modal-footer">
        <slot name="modal-footer" />
        <button
          class="btn btn-primary"
          @click="$emit('close-modal')"
        >
          <slot name="modal-close-btn">
            Close
          </slot>
        </button>
      </div>
    </div>
  </div>
</template>
  
<script>
export default {
  name: 'AccountModal',
  props: {
    isOpen: {
      type: Boolean,
      default: false
    }
  }
}
</script>

<style scoped>
/* Add your styles here if needed */
</style>