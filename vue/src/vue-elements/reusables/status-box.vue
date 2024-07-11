<template>
  <div
    class="sharing-box"
    :class="statusColorClass"
  >
    <svg
      v-if="'sharing-status-sharing' === statusColorClass"
      width="20"
      height="20"
      viewBox="0 0 20 20"
      fill="none"
      xmlns="http://www.w3.org/2000/svg"
    >
      <path
        d="M8.00002 18.777C7.12963 18.5796 6.2937 18.253 5.52002 17.808M12 1.22302C13.9882 1.67709 15.7632 2.79271 17.0347 4.38723C18.3061 5.98174 18.9984 7.96067 18.9984 10C18.9984 12.0394 18.3061 14.0183 17.0347 15.6128C15.7632 17.2073 13.9882 18.323 12 18.777M2.57899 15.093C2.03349 14.3005 1.61919 13.4253 1.35199 12.501M1.12402 8.50002C1.28402 7.55002 1.59202 6.65002 2.02402 5.82502L2.19302 5.52002M4.90698 2.57902C5.84264 1.93492 6.8923 1.47474 7.99998 1.22302M7 10L9 12L13 8.00003"
        stroke="#218818"
        stroke-width="2"
        stroke-linecap="round"
        stroke-linejoin="round"
      />
    </svg>
    <svg
      v-if="'sharing-status-sharing' !== statusColorClass"
      width="20"
      height="20"
      viewBox="0 0 20 20"
      fill="none"
      xmlns="http://www.w3.org/2000/svg"
    >
      <path
        d="M8.00002 18.777C7.12963 18.5796 6.2937 18.253 5.52002 17.808M12 1.22302C13.9882 1.67709 15.7632 2.79271 17.0347 4.38723C18.3061 5.98174 18.9984 7.96067 18.9984 10C18.9984 12.0394 18.3061 14.0183 17.0347 15.6128C15.7632 17.2073 13.9882 18.323 12 18.777M2.57899 15.093C2.03349 14.3005 1.61919 13.4253 1.35199 12.501M1.12402 8.50002C1.28402 7.55002 1.59202 6.65002 2.02402 5.82502L2.19302 5.52002M4.90698 2.57902C5.84264 1.93492 6.8923 1.47474 7.99998 1.22302M10 6.00003V10M10 14V14.01"
        :stroke="'sharing-status-nosharing' === statusColorClass ? '#B33E00' : '#3A3A3A'"
        stroke-width="2"
        stroke-linecap="round"
        stroke-linejoin="round"
      />
    </svg>

    <span>
      <b>{{ title }}:</b>
      {{ description }}
    </span>
  </div>
</template>

<script>
export default {
  name: 'StatusBox',
  props: {
    statusColorClass: {
      type: String,
      required: true
    },
    label: {
      type: String,
      required: true
    }
  },
  data() {
    const [ title, description ] = this.label.split( ':' )
    return {
      title,
      description
    }
  }
}
</script>

<style scoped>
span {
  font-weight: 400;
}

.sharing-box{
  padding: 15px 10px;
  font-size: 0.75rem;
  margin: 0 0 10px 0;
  text-align: center;
}

.sharing-status-sharing {
  color: #144E0F;
  background-color: #E7F7E6;
}

.sharing-status-notsharing {
  color: #3A3A3A;
  background-color: #F1F1F1;
}

.sharing-status-error {
  color: #3A3A3A;
  background-color: #FFEBE0;
}
</style>

<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
  <path d="M8.00002 18.777C7.12963 18.5796 6.2937 18.253 5.52002 17.808M12 1.22302C13.9882 1.67709 15.7632 2.79271 17.0347 4.38723C18.3061 5.98174 18.9984 7.96067 18.9984 10C18.9984 12.0394 18.3061 14.0183 17.0347 15.6128C15.7632 17.2073 13.9882 18.323 12 18.777M2.57899 15.093C2.03349 14.3005 1.61919 13.4253 1.35199 12.501M1.12402 8.50002C1.28402 7.55002 1.59202 6.65002 2.02402 5.82502L2.19302 5.52002M4.90698 2.57902C5.84264 1.93492 6.8923 1.47474 7.99998 1.22302M10 6.00003V10M10 14V14.01" stroke="#3A3A3A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
  </svg>
  
