<template>
  <div class="flexible-content">
    <Header></Header>
    <main>
		<transition
			name="fade"
			mode="out-in"
		>
		  <router-view></router-view>
		</transition>
    </main>
  </div>
</template>

<script>
	export default {
		name: 'App'
	}
</script>

<style>
.fade-enter-active,
.fade-leave-active {
  transition-duration: 0.3s;
  transition-property: opacity;
  transition-timing-function: ease;
}

.fade-enter,
.fade-leave-active {
  opacity: 0
}
</style>
