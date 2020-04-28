<template>
	<div id="cac-group-library-inner">
		<div class="add-new-header">
			<div class="back-to-library">
				<span class="dashicons dashicons-arrow-left-alt"></span> <router-link to="/">Back to Library</router-link>
			</div>

			<h2>New Library Item</h2>
		</div>

		<div class="add-new-content">
			<p class="add-new-content-intro">
				You can select three different types of items to add to your Library. Files are standalone uploads from your desktop like images, PDFs or Word Docs (similar to an email attachment). Docs are editable documents you can create and edit via your Library later. External Links point to an item hosted elsewhere on the web, like Google, Dropbox, or Microsoft OneDrive.
			</p>

			<ItemTypeDropdown />

			<div class="add-new-content-form">
				<transition name="fade" mode="out-in">
					<AddNewBpDoc v-if="isBpDoc" />
					<AddNewBpGroupDocument v-if="isBpGroupDocument" />
					<AddNewExternalLink v-if="isExternalLink" />
				</transition>
			</div>

		</div>
	</div>
</template>

<script>
	import AddNewBpDoc from '../components/Forms/AddNewBpDoc.vue'
	import AddNewBpGroupDocument from '../components/Forms/AddNewBPGroupDocument.vue'
	import AddNewExternalLink from '../components/Forms/AddNewExternalLink.vue'
	import ItemTypeDropdown from '../components/ItemTypeDropdown.vue'

	export default {
		components: {
			AddNewBpDoc,
			AddNewBpGroupDocument,
			AddNewExternalLink,
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

	.add-new-content {
		border-top: 1px solid #ccc;
		margin-top: 20px;
		padding-top: 20px;
	}

	.add-new-form {
		margin-top: 20px;
		max-width: 690px;
	}

	.add-new-field {
		margin-bottom: 20px;
	}

	.add-new-field label {
		display: block;
		font-weight: bold;
	}

	.add-new-field-text input {
		border: 1px solid #ccc;
		border-radius: 4px;
		font-size: 12px;
		line-height: 35px;
		padding: 0 8px;
		width: calc(100% - 16px);
	}

	.add-new-field p.description {
		font-style: italic;
	}
</style>
