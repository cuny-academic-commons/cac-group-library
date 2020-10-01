<template>
	<div class="group-library-dialog delete-folder-dialog">
		<a
			class="upper-right button"
			v-on:click="onCloseClick()"
		>x</a>

		<h2>{{ messageBody }}</h2>

		<p>Choose between the following options:</p>

		<div class="dialog-action">
			<a
				class="button"
				v-on:click="onDeleteFolderAndContentsClick()"
			>Delete folder and its contents</a>
			<p class="description">This action cannot be reversed.</p>
		</div>

		<div class="dialog-action">
			<a
				class="button"
				v-on:click="onDeleteFolderOnlyClick()"
			>Delete folder but keep its contents</a>
			<p class="description">Contents will be placed in the top level of the Library.</p>
		</div>
	</div>
</template>

<script>
	import DialogMixin from 'vuejs-dialog/dist/vuejs-dialog-mixin.min.js'
	import AjaxTools from '../mixins/AjaxTools.js'

	export default {
		methods: {
			onCloseClick() {
				this.cancel()
			},

			onDeleteFolderAndContentsClick() {
				this.proceed( 'deleteFolderAndContents' )
			},

			onDeleteFolderOnlyClick() {
				this.proceed( 'deleteFolderOnly' )
			},
		},

		mixins: [
			AjaxTools,
			DialogMixin,
		]
	}
</script>

<style>
	.delete-folder-dialog {
		padding-left: 10px;
		position: relative;
	}

	a.upper-right {
		position: absolute;
		right: 0px;
		top: 0px;
	}

	.delete-folder-dialog h2 {
		font-size: 16px;
		margin-bottom: 20px;
		padding-right: 50px;
	}

	.delete-folder-dialog p.description {
		font-style: italic;
		margin-top: 8px;
	}

	.dialog-action {
		margin-bottom: 25px;
	}

	.dialog-action:last-child {
		margin-bottom: 0;
	}
</style>
