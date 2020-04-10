<template>
	<div class="library-item group-library-row">
		<div class="group-library-item-title">
			{{ title() }}
		</div>

		<div class="group-library-item-added-by">
			<a :href="addedByUrl()">{{ addedByName() }}</a>
		</div>

		<div class="group-library-item-date">
			{{ date() }}
		</div>
	</div>
</template>

<script>
	export default {
		methods: {
			addedByName() {
				return this.getItem().user.name
			},

			addedByUrl() {
				return this.getItem().user.url
			},

			date() {
				const d = new Date( this.getItem().date_modified )
				const dtf = new Intl.DateTimeFormat('en-US').format( d )
				return dtf
			},

			getItem() {
				return this.$store.state.libraryItems[ this.itemId ]
			},

			title() {
				return this.getItem().title
			}
		},

		props: [
			'itemId'
		]
	}
</script>

<style>

</style>
