<template>
	<div id="cac-group-library-inner">
		<div class="add-new-header">
			<div class="back-to-library">
				<span class="dashicons dashicons-arrow-left-alt"></span> <router-link to="/">Back to Library</router-link>
			</div>

			<h2>New Library Item</h2>
		</div>

		<div class="add-edit-content add-new-content">
			<p class="add-new-content-intro">
				You can select three different types of items to add to your Library. Files are standalone uploads from your desktop like images, PDFs or Word Docs (similar to an email attachment). Docs are editable documents you can create and edit via your Library later. External Links point to an item hosted elsewhere on the web, like Google, Dropbox, or Microsoft OneDrive.
			</p>

			<ItemTypeDropdown />

			<div class="add-new-content-form">
				<transition name="fade" mode="out-in">
					<BpDocForm v-if="isBpDoc" />
					<BpGroupDocumentForm v-if="isBpGroupDocument" />
					<ExternalLinkForm v-if="isExternalLink" />
				</transition>
			</div>
		</div>
	</div>
</template>

<script>
	import BpDocForm from '../components/Forms/BpDocForm.vue'
	import BpGroupDocumentForm from '../components/Forms/BpGroupDocumentForm.vue'
	import ExternalLinkForm from '../components/Forms/ExternalLinkForm.vue'
	import ItemTypeDropdown from '../components/ItemTypeDropdown.vue'

	export default {
		components: {
			BpDocForm,
			BpGroupDocumentForm,
			ExternalLinkForm,
			ItemTypeDropdown
		},

		computed: {
			isExternalLink() {
				return this.selectedItemType === 'externalLink'
			},

			isBpDoc() {
				return this.selectedItemType === 'bpDoc'
			},

			isBpGroupDocument() {
				return this.selectedItemType === 'bpGroupDocument'
			},

			selectedItemType() {
				return this.$store.state.forms.itemTypeSelector
			}
		}
	}
</script>

<style>
	.back-to-library {
		font-size: 12px;
		line-height: 20px;
		margin-bottom: 18px;
	}

	.back-to-library .dashicons {
		color: #1c576c;
		font-size: 14px;
		line-height: 20px;
	}

	.add-edit-content {
		border-top: 1px solid #ccc;
		margin-top: 20px;
		padding-top: 20px;
	}

	.add-new-form {
		margin-top: 20px;
		max-width: 690px;
	}
</style>
