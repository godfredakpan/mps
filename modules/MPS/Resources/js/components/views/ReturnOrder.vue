<template>
  <div class="fullpage">
    <div v-if="loading" class="preloaderapp" ref="preloaderapp">
      <div>
        <div class="spin spin-large spin-default">
          <div class="spin-main">
            <div v-if="$store.state.settings.loader == 'circle'" class="spin-text">
              <div class="circle">
                <svg viewBox="25 25 50 50" class="rotating-circle">
                  <circle cx="50" cy="50" r="20" fill="none" stroke-width="5" stroke-miterlimit="10" class="rotating-circle-path"></circle>
                </svg>
              </div>
            </div>
            <div v-else>
              <span class="spin-dot"></span>
              <div class="spin-text"></div>
            </div>
          </div>
        </div>
        <div class="error" style="text-align:center;margin-top:2rem;font-size:1.2rem;color:#3F4448;display:none">
          <svg
            version="1.1"
            xmlns="http://www.w3.org/2000/svg"
            xmlns:xlink="http://www.w3.org/1999/xlink"
            x="0px"
            y="0px"
            viewBox="0 0 451.74 451.74"
            style="width:100px;height:100px;"
          >
            <path
              style="fill:#E24C4B;"
              d="M446.324,367.381L262.857,41.692c-15.644-28.444-58.311-28.444-73.956,0L5.435,367.381 c-15.644,28.444,4.267,64,36.978,64h365.511C442.057,429.959,461.968,395.825,446.324,367.381z"
            />
            <path style="fill:#FFFFFF;" d="M225.879,63.025l183.467,325.689H42.413L225.879,63.025L225.879,63.025z" />
            <g>
              <path
                style="fill:#3F4448;"
                d="M196.013,212.359l11.378,75.378c1.422,8.533,8.533,15.644,18.489,15.644l0,0 c8.533,0,17.067-7.111,18.489-15.644l11.378-75.378c2.844-18.489-11.378-34.133-29.867-34.133l0,0 C207.39,178.225,194.59,193.87,196.013,212.359z"
              />
              <circle style="fill:#3F4448;" cx="225.879" cy="336.092" r="17.067" />
            </g></svg
          ><br />
          <span>Network Error<br /></span>
          <span style="color:#E24C4B">Unable to load application.</span>
        </div>
      </div>
    </div>
    <div v-else class="order-view">
      <order-view-component
        :record="return_order"
        :heading="$tc('return_order')"
        :field="return_order.type == 'sale' ? 'price' : 'cost'"
        :to-text="return_order.type == 'sale' ? $tc('from') : $tc('to')"
      />
    </div>
  </div>
</template>

<script>
import OrderViewComponent from '@mpscom/helpers/OrderViewComponent';

export default {
  components: { OrderViewComponent },
  data() {
    return {
      loading: true,
      return_order: {},
    };
  },
  created() {
    if (this.$route.params.hash) {
      this.loading = true;
      this.$http
        .get(`/views/return_order/${this.$route.params.hash}`)
        .then(res => {
          this.return_order = res.data;
          this.$nextTick(() => {
            document.title = this.$tc('return_order') + ' ' + this.return_order.reference;
          });
        })
        .catch(err => {
          this.$Notice.error({ title: this.$t('not_found'), desc: this.$t('not_found_text') });
          this.$router.push('/views');
        })
        .finally(() => (this.loading = false));
    } else {
      this.$router.push('/views');
    }
  },
};
</script>
