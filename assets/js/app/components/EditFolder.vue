<template>
	<div>
		<div v-if="isEditMode">
			{{ folderName }} edit mode

			<button v-on:click="onCancelClick()">
				Cancel
			</button>
		</div>

		<div v-else>
			{{ folderName }} not edit mode
			<button v-on:click="onEditClick()">
				Rename
			</button>
		</div>

	</div>
</template>

<script>
	export default {
		computed: {
			isEditMode: {
				get() {
					return this.folderName === this.$store.state.currentlyEditedFolder
				},

				set( value ) {
					const newValue = value ? this.folderName : ''
					this.$store.commit( 'setCurrentlyEditedFolder', { value: newValue } )
				}
			}
		},

		methods: {
			onCancelClick() {
				this.isEditMode = false
			},

			onEditClick() {
				console.log('ok');
				this.isEditMode = true
			},

		},

		props: {
			folderName: String
		}
	}
</script>
