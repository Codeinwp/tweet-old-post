<template>
  <AccountModal
    :is-open="modal.isOpen"
    @close-modal="closeModal"
    @cancel-modal="cancelModal"
  >
    <template #modal-title>
      <h3>{{ modal.serviceName }}</h3>
    </template>
    <template #modal-content>
      <div
        v-for="(field, id) in modal.data"
        :key="field.id"
        class="form-group"
      >
        <label
          class="form-label"
          :for="field.id"
        >{{ field.name }}</label>
        <input
          :id="field.id"
          v-model="field.value"
          :class="[ 'form-input', field.error ? ' is-error' : '' ]"
          type="text"
          :placeholder="field.name"
        >
        <small
          v-if="field.error"
          class="text-error"
        >
          {{ labels.field_required }}
        </small>
        <p class="text-gray uppercase">
          {{ field.description }}
        </p>
      </div>
    </template>
    <template #modal-extra>
      <WebhookHeaders
        v-model="headers"
      />
    </template>
    <template #modal-footer>
      <button
        class="btn btn-primary"
        @click="saveWebhookConfig"
      >
        Save
      </button>
    </template>
  </AccountModal>
</template>
  
<script>
import WebhookHeaders from './webhook-headers.vue';
import AccountModal from './account-modal.vue';

export default {
    name: 'WebhookAccountModal',
    components: {
        WebhookHeaders,
        AccountModal
    },
    props: {
        initialHeaders: {
          type: Array,
          default: () => []
        }
    },
    data() {
        return {
            modal: {
                isOpen: true,
                serviceName: 'Webhook',
                description: 'Configure your webhook',
                data: {}
            },
            webhookUrl: '',
            headers: []
        }
    },
    methods: {
        openModal() {
            this.modal.isOpen = true;
            this.headers = [...this.initialHeaders];
        },
        closeModal() {
            this.$emit('close-modal');
            this.modal.isOpen = false;
        },
        cancelModal() {
            this.$store.state.auth_in_progress = false;
            this.$emit('cancel-modal');
            this.modal.isOpen = false;
            this.resetForm();
        },
        updateHeaders(newHeaders) {
            this.headers = newHeaders;
        },
        saveWebhookConfig() {
            const config = {
                url: this.webhookUrl,
                headers: this.headers
            };
            this.$emit('save-webhook', config);
            this.closeModal();
        },
        resetForm() {
            this.webhookUrl = '';
            this.headers = [...this.initialHeaders];
        }
    }
}
</script>

<style scoped>
/* Styles remain unchanged */
</style>

  