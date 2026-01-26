<template>
	<div class="file-uploader">
		<!-- Upload Zone - shown when no file is selected -->
		<div
			v-if="!hasFile"
			class="upload-zone"
			:class="{ 'drag-over': isDragging }"
			@drop.prevent="handleDrop"
			@dragover.prevent="handleDragOver"
			@dragleave.prevent="handleDragLeave"
			@click="openFilePicker"
		>
			<div class="upload-zone-content">
				<p class="upload-zone-text">Drop your file here</p>
				<p class="upload-zone-or">or</p>
				<button type="button" class="upload-zone-button">Choose File</button>
				<p class="upload-zone-hint" v-if="maxSizeText">{{ maxSizeText }}</p>
			</div>
		</div>

		<!-- Preview Zone - shown when file is selected -->
		<div v-else class="file-preview">
			<div class="file-preview-container">
				<!-- Image preview -->
				<div v-if="isImage" class="file-preview-image-wrapper">
					<img :src="previewUrl" alt="File preview" class="file-preview-image" />
				</div>

				<!-- Non-image file icon -->
				<div v-else class="file-preview-icon-wrapper">
					<img :src="fileIconUrl" alt="File type icon" class="file-preview-icon" />
				</div>

				<!-- File info -->
				<div class="file-preview-info">
					<p class="file-preview-name">{{ fileName }}</p>
					<p class="file-preview-url" v-if="existingFileUrl">
						<strong>Attached File URL</strong><br>
						<a :href="existingFileUrl" target="_blank">{{ existingFileUrl }}</a>
					</p>
				</div>
			</div>

			<!-- Change file button -->
			<button type="button" class="file-change-button" @click="changeFile">
				Change File
			</button>
		</div>

		<!-- Hidden file input -->
		<input
			ref="fileInput"
			type="file"
			:accept="acceptFiletypes"
			:required="required"
			@change="handleFileSelect"
			style="display: none;"
		/>
	</div>
</template>

<script>
	export default {
		data() {
			return {
				isDragging: false,
				selectedFile: null,
				previewUrl: null
			}
		},

		computed: {
			acceptFiletypes() {
				const { uploadFiletypes } = window.CACGroupLibrary
				return uploadFiletypes.map( type => '.' + type ).join( ',' )
			},

			existingFileUrl() {
				// Only show existing file URL in edit mode
				if (!this.isEditMode || !this.itemId) {
					return null
				}

				const item = this.$store.state.libraryItems[this.itemId]
				return item && item.url ? item.url : null
			},

			fileName() {
				if (this.selectedFile) {
					return this.selectedFile.name
				}

				if (this.existingFileUrl) {
					// Extract filename from URL
					const parts = this.existingFileUrl.split('/')
					return parts[parts.length - 1]
				}

				return ''
			},

			fileExtension() {
				const name = this.fileName
				const lastDot = name.lastIndexOf('.')
				return lastDot > -1 ? name.substring(lastDot + 1).toLowerCase() : ''
			},

			fileIconUrl() {
				const iconBase = window.CACGroupLibrary.iconUrlBase
				const iconName = this.getFileTypeIcon() + '.svg'
				return iconBase + iconName
			},

			hasFile() {
				return this.selectedFile !== null || (this.isEditMode && this.existingFileUrl)
			},

			isEditMode() {
				return this.itemId && this.itemId > 0
			},

			isImage() {
				const imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg', 'webp']
				return imageExtensions.includes(this.fileExtension)
			},

			maxSizeText() {
				const { maxUploadSizeFormatted } = window.CACGroupLibrary
				return maxUploadSizeFormatted ? `(Maximum file size allowed is ${maxUploadSizeFormatted})` : ''
			}
		},

		methods: {
			changeFile() {
				this.openFilePicker()
			},

			getFileTypeIcon() {
				const ext = this.fileExtension

				switch (ext) {
					case 'pdf':
						return 'pdf'
					case 'xls':
					case 'xlsx':
						return 'excel'
					case 'doc':
					case 'docx':
						return 'word'
					case 'ppt':
					case 'pptx':
						return 'ppt'
					case 'mp3':
					case 'wav':
					case 'ogg':
						return 'audio'
					case 'mp4':
					case 'avi':
					case 'mov':
						return 'video'
					case 'zip':
					case 'rar':
					case '7z':
						return 'zip'
					case 'jpg':
					case 'jpeg':
					case 'png':
					case 'gif':
					case 'bmp':
					case 'svg':
					case 'webp':
						return 'image'
					default:
						return 'general'
				}
			},

			handleDragLeave() {
				this.isDragging = false
			},

			handleDragOver() {
				this.isDragging = true
			},

			handleDrop(e) {
				this.isDragging = false
				const files = e.dataTransfer.files
				if (files.length > 0) {
					this.processFile(files[0])
				}
			},

			handleFileSelect(e) {
				const files = e.target.files
				if (files.length > 0) {
					this.processFile(files[0])
				}
			},

			openFilePicker() {
				this.$refs.fileInput.click()
			},

			processFile(file) {
				this.selectedFile = file

				// Create preview URL for images
				if (this.isImage) {
					// Revoke old preview URL to prevent memory leaks
					if (this.previewUrl) {
						URL.revokeObjectURL(this.previewUrl)
					}
					this.previewUrl = URL.createObjectURL(file)
				}

				// Notify parent component/store about the file selection
				this.$emit('file-selected', file)

				// Update store if formName and fieldName are provided
				if (this.formName && this.fieldName) {
					this.$store.commit('setFormFieldValue', {
						form: this.formName,
						field: this.fieldName,
						value: file
					})
				}
			}
		},

		beforeDestroy() {
			// Clean up preview URL to prevent memory leaks
			if (this.previewUrl) {
				URL.revokeObjectURL(this.previewUrl)
			}
		},

		props: {
			fieldName: String,
			formName: String,
			itemId: Number,
			required: {
				type: Boolean,
				default: false
			}
		}
	}
</script>

<style scoped>
	.file-uploader {
		margin-bottom: 20px;
	}

	.upload-zone {
		border: 2px dashed #ccc;
		border-radius: 4px;
		padding: 40px 20px;
		text-align: center;
		cursor: pointer;
		transition: all 0.3s ease;
		background-color: #fafafa;
	}

	.upload-zone:hover {
		border-color: #999;
		background-color: #f5f5f5;
	}

	.upload-zone.drag-over {
		border-color: #4a90e2;
		background-color: #e8f4fd;
	}

	.upload-zone-content {
		pointer-events: none;
	}

	.upload-zone-text {
		font-size: 16px;
		font-weight: 500;
		margin: 0 0 10px 0;
		color: #333;
	}

	.upload-zone-or {
		margin: 10px 0;
		color: #666;
	}

	.upload-zone-button {
		background-color: #333;
		color: #fff;
		border: none;
		padding: 10px 20px;
		font-size: 14px;
		cursor: pointer;
		border-radius: 3px;
		pointer-events: auto;
	}

	.upload-zone-button:hover {
		background-color: #555;
	}

	.upload-zone-hint {
		margin-top: 15px;
		font-size: 12px;
		font-style: italic;
		color: #666;
	}

	.file-preview {
		border: 1px solid #ddd;
		border-radius: 4px;
		padding: 20px;
		background-color: #fafafa;
	}

	.file-preview-container {
		display: flex;
		align-items: flex-start;
		gap: 20px;
		margin-bottom: 15px;
	}

	.file-preview-image-wrapper {
		flex-shrink: 0;
		max-width: 200px;
	}

	.file-preview-image {
		max-width: 100%;
		max-height: 200px;
		border-radius: 4px;
		border: 1px solid #ddd;
	}

	.file-preview-icon-wrapper {
		flex-shrink: 0;
		width: 80px;
		height: 80px;
		display: flex;
		align-items: center;
		justify-content: center;
	}

	.file-preview-icon {
		width: 60px;
		height: auto;
	}

	.file-preview-info {
		flex: 1;
		min-width: 0;
	}

	.file-preview-name {
		font-weight: 600;
		margin: 0 0 10px 0;
		word-wrap: break-word;
		color: #333;
	}

	.file-preview-url {
		margin: 0;
		font-size: 14px;
		color: #666;
	}

	.file-preview-url strong {
		display: block;
		margin-bottom: 5px;
		color: #333;
	}

	.file-preview-url a {
		word-wrap: break-word;
		color: #4a90e2;
		text-decoration: none;
	}

	.file-preview-url a:hover {
		text-decoration: underline;
	}

	.file-change-button {
		background-color: #666;
		color: #fff;
		border: none;
		padding: 8px 16px;
		font-size: 14px;
		cursor: pointer;
		border-radius: 3px;
	}

	.file-change-button:hover {
		background-color: #777;
	}

	@media (max-width: 768px) {
		.file-preview-container {
			flex-direction: column;
		}

		.file-preview-image-wrapper {
			max-width: 100%;
		}

		.file-preview-image {
			max-width: 100%;
		}
	}
</style>
