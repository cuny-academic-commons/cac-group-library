<template>
	<div>This is living</div>
</template>

<script>
	export default {
		computed: {
			canEditFolders() {
				return this.$store.state.canEditFolders
			},

			foldersOfGroup() {
				return this.$store.state.foldersOfGroup
			},

			itemIds() {
				return this.$store.state.libraryItemIds
			},
		}
	}
</script>
