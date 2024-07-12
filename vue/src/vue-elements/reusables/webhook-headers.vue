<template>
  <div class="webhook-headers">
    <h6>HTTP Headers</h6>
    <div
      v-for="(header, index) in localHeaders"
      :key="index"
      class="webhook-header"
    >
      <input
        v-model="localHeaders[index]"
        type="text"
        class="form-input"
        placeholder="Authorization: Bearer XXXXXXXXXXXXXX"
        @input="updateHeaders"
      >
      <button
        class="btn btn-danger"
        aria-label="Remove header"
        @click="removeHeader(index)"
      >
        <span aria-hidden="true">&times;</span>
      </button>
    </div>
    <div class="webhook-header">
      <input
        v-model="newHeader"
        type="text"
        class="form-input"
        placeholder="Authorization: Bearer XXXXXXXXXXXXXX"
        @keyup.enter="addWebhookHeader"
      >
      <button
        class="btn btn-primary"
        @click="addWebhookHeader"
      >
        Add Header
      </button>
    </div>
  </div>
</template>

<script>
export default {
  name: 'WebhookHeaders',
  props: {
    headers: {
      type: Array,
      default: () => [],
    },
  },
  data() {
    return {
      localHeaders: [],
      newHeader: '',
    }
  },
  watch: {
    headers: {
      immediate: true,
      handler(newHeaders) {
        this.localHeaders = [...newHeaders];
      }
    }
  },
  methods: {
    addWebhookHeader() {
      const trimmedHeader = this.newHeader.trim();
      if (trimmedHeader) {
        this.localHeaders.push(trimmedHeader);
        this.newHeader = '';
        this.updateHeaders();
      }
    },
    removeHeader(index) {
      this.localHeaders.splice(index, 1);
      this.updateHeaders();
    },
    updateHeaders() {
      this.$emit('update:headers', [...this.localHeaders]);
    }
  }
}
</script>

<style scoped>
  .webhook-headers {
    background-color: #f7f7f7;
    padding: 10px;
    min-width: 400px;
    border-radius: 10px;

    display: flex;
    flex-direction: column;
    gap: 10px;
  }

  .webhook-header {
    display: flex;
    flex-direction: row;
    align-items: center;
    justify-content: space-around;
    gap: 5px;
  }

  .webhook-header:has(.btn-primary) {
    flex-direction: column;
  }
</style>