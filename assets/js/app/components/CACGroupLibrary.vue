<template>
	<div id="cac-group-library-inner">
		<div class="group-library-header">
			<h2>Library</h2>

		</div>
		Success!
	</div>
</template>

<script>
	/*
	import PanelTools from '../mixins/PanelTools.js'

	import ModalContent from './ModalContent.vue'
	import ModalHeader from './ModalHeader.vue'
	import ModalNav from './ModalNav.vue'
	*/

	export default {
		/*
		components: {
			ModalContent,
			ModalHeader,
			ModalNav
		},
		*/

		methods: {
			ariaHidden() {
				return ! this.$store.state.modalIsVisible ? 'true' : 'false'
			},

			overlayClass() {
				return {
					'caco-modal-hidden': ! this.$store.state.modalIsVisible,
					'caco-modal-visible': this.$store.state.modalIsVisible,
				}
			}
		},

		/*
		mixins: [
			PanelTools
		],
		*/
	}
</script>

<style>
body.groups.single-item.library #item-header {
	display: none;
}

#cac-group-library-inner {
	margin-top: 24px;
}
</style>
