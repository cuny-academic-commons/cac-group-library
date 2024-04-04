<template>
	<div class="group-library-filter-dropdown">
		<label
			v-bind:for="fieldId"
			class="screen-reader-only">
			{{title}}
		</label>

		<v-select
			v-bind:clearable="false"
			v-bind:id="fieldId"
			v-model="selected"
			:options="opts">

			<template #option="{code, label}">
				<div v-if="'_edit' === code" class="edit-filter-option">
					<span class="dashicons dashicons-plus"></span> {{ label }}
				</div>
				<div v-else class="">
					{{ label }}
				</div>
			</template>

			<template #selected-option="{code, label}">
				<span v-if="'_edit' === code" class="edit-filter-option-selected">
					<span class="dashicons dashicons-plus"></span> {{ label }}
				</span>

				<span v-else>
					{{ label }}
				</span>
			</template>
		</v-select>
	</div>
</template>

<script>
	import vSelect from 'vue-select'
	import 'vue-select/dist/vue-select.css';

	export default {
		components: {
			vSelect
		},

		computed: {
			fieldId() {
				return 'filter-' + this.name
			},

			selected: {
				get: function() {
					return this.getCurrentCallback()
				},

				set: function( payload ) {
					this.setCurrentCallback( payload )

					this.$store.commit( 'refresh' )
				}
			},
		},

		props: {
			getCurrentCallback: Function,
			name: String,
			opts: Array,
			setCurrentCallback: Function,
			title: String,
		}
	}
</script>

<style>
.v-select {
	display: inline-block;
	width: 200px;
}

.group-library-filter-dropdown {
	align-items: center;
	display: flex;
	gap: 8px;
}

@media screen and (max-width:600px) {
	.v-select {
		display: block;
		margin-bottom: 10px;
		width: 100%;
	}
}
</style>
