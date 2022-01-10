<template>
  <div class="error404">
    <div class="error404-body-con">
      <Card :dis-hover="true">
        <div class="error404-body-con-title">
          4
          <span>
            <Icon type="md-navigate" />
          </span>
          4
        </div>
        <p class="error404-body-con-message">YOU&nbsp;&nbsp;LOOK&nbsp;&nbsp;LOST</p>
        <div class="error404-btn-con">
          <Button @click="goHome" size="large" style="width: 200px;">Home</Button>
          <Button @click="backPage" size="large" style="width: 200px; margin-left: 40px;" type="primary">Back</Button>
        </div>
      </Card>
    </div>
  </div>
</template>

<script>
export default {
  name: 'Error404',
  methods: {
    backPage() {
      this.$router.back();
    },
    goHome() {
      this.$router.push('/');
    },
  },
};
</script>

<style>
@keyframes error404animation {
  0% {
    transform: rotate(0deg);
  }
  20% {
    transform: rotate(-60deg);
  }
  40% {
    transform: rotate(-10deg);
  }
  60% {
    transform: rotate(50deg);
  }
  80% {
    transform: rotate(-20deg);
  }
  100% {
    transform: rotate(0deg);
  }
}
.error404 {
  height: 100vh;
  display: flex;
  min-height: 540px;
  align-items: center;
  justify-content: center;
}
.error404-body-con {
  width: 700px;
  height: 500px;
}
.error404-body-con-title {
  height: 260px;
  color: #2d8cf0;
  font-size: 240px;
  font-weight: 700;
  margin-top: 40px;
  text-align: center;
  line-height: 260px;
  letter-spacing: -0.1em;
}
.error404-body-con-title span {
  width: 180px;
  height: 180px;
  color: #19be6b;
  font-size: 100px;
  margin-top: 25px;
  margin-left: 25px;
  border-radius: 150px;
  display: inline-block;
  border: 10px solid #19be6b;
  animation: error404animation 3s ease 0s infinite alternate;
}
.error404-body-con-title span i {
  top: 50%;
  left: 50%;
  width: 50px;
  height: 100px;
  margin-top: -50px;
  margin-left: -50px;
  position: absolute;
  display: inline-block;
}
.error404-body-con-message {
  color: #dddde2;
  display: block;
  font-size: 30px;
  font-weight: 500;
  text-align: center;
  letter-spacing: 12px;
}
.error404-btn-con {
  padding: 20px 0;
  text-align: center;
  margin-bottom: 40px;
}
@media (max-width: 768px) {
  .error404-body-con-title {
    font-size: 170px;
  }
  .error404-body-con-title span {
    width: 120px;
    height: 120px;
    margin-top: 25px;
    margin-left: 20px;
  }
  .error404-body-con-title span i {
    width: 25px;
    height: 50px;
    font-size: 0.5em;
    margin-top: -25px;
    margin-left: -26px;
  }
  .error404-body-con {
    transform: scale(0.75);
  }
}
@media (max-width: 500px) {
  .error404-btn-con button {
    width: 90%;
    margin: 5% !important;
  }
  .error404-body-con {
    transform: scale(0.6);
  }
}
</style>
