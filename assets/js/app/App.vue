<template>
  <div class="flexible-content">
    <Header></Header>
    <main>
      <router-view></router-view>
    </main>
  </div>
</template>

<script>
	export default {
		name: 'App'
	}
</script>
