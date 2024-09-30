<template>
	<div id="cac-group-library-inner" class="cac-group-library-inner">
		<div class="back-to-library">
			<span class="dashicons dashicons-arrow-left-alt2"></span> <router-link to="/">Back to Library</router-link>
		</div>

		<div class="cac-group-library-inner-content">
			<div class="add-new-header">
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
	.cac-group-library-inner {
		margin-top: 24px;
	}

	.cac-group-library-inner-content {
		background: #fff;
		padding: 2em 4em;
	}

	.back-to-library {
		line-height: 20px;
		margin-bottom: 18px;
	}

	.directory-content .back-to-library a {
		text-decoration: underline;
	}

	.directory-content .back-to-library a:hover {
		text-decoration: none;
	}

	.back-to-library .dashicons {
		font-size: 20px;
		line-height: 20px;
		margin-left: -4px;
		padding-right: 6px;
	}

	.add-edit-content {
		margin-top: 30px;
	}

	.add-new-content {
		max-width: 860px;
	}

	.add-new-form {
		margin-top: 20px;
		max-width: 690px;
	}
</style>
