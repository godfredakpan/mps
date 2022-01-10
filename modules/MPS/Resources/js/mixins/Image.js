import ImageComponent from '@mpscom/helpers/ImageComponent';

export default {
  components: {
    ImageComponent,
  },
  data() {
    return {
      form: { image: null },
    };
  },
  methods: {
    handleImageUpload(image) {
      this.form.image = image;
      return false;
    },
    clearSelectedImage() {
      this.form.image = null;
    },
  },
};
