<template>
	<div class="library-search">
		<input
			class="library-search-input"
			placeholder="Enter text to search library"
			v-bind:style="backgroundStyles()"
			v-on:focus="onFocus()"
			v-model="currentSearchTerm"
		/>

		<span
			class="dashicons dashicons-no-alt search-input-click-to-close"
			v-if="isSearchExpanded"
			v-on:click="onCloseClick()"
		></span>
	</div>
</template>

<script>
	export default {
		computed: {
			currentSearchTerm: {
				get() {
					return this.$store.state.currentSearchTerm
				},

				set( value ) {
					this.$store.commit(
						'setCurrentSearchTerm',
						{ value }
					)

					this.$store.commit( 'refreshFilteredItemIds' )
				}
			},

			isSearchExpanded: {
				get() {
					return this.$store.state.isSearchExpanded
				},

				set( value ) {
					this.$store.commit(
						'setIsSearchExpanded',
						{ value }
					)

					this.$store.commit(
						'setShowDescriptions',
						{ value }
					)

					// Required to trigger pagination.
					this.$store.commit( 'refreshFilteredItemIds' )
				}
			}
		},

		methods: {
			backgroundStyles() {
				const { imgUrlBase } = window.CACGroupLibrary;

				if ( this.isSearchExpanded ) {
					return {
						'background-image': 'url( ' + imgUrlBase + 'search.svg )',
						'background-position': 'center left 8px',
						'background-repeat': 'no-repeat',
						'background-size': '12px',
						'padding-left': '28px'
					}

				} else {
					return {
						'background-image': 'url( ' + imgUrlBase + 'search.svg )',
						'background-position': 'center right 8px',
						'background-repeat': 'no-repeat',
						'background-size': '12px',
						'opacity': '.75',
					}
				}
			},

			onCloseClick( event ) {
				this.$store.commit(
					'setCurrentSearchTerm',
					{ value: '' }
				)

				this.$store.commit(
					'setIsSearchExpanded',
					{ value: false }
				)

				this.$store.commit(
					'setShowDescriptions',
					{ value: false }
				)

				this.$store.commit( 'refreshFilteredItemIds' )

				return false
			},

			onFocus() {
				this.isSearchExpanded = true
			}
		}
	}
</script>

<style>
.library-search-input {
	background: url( ../../../img/search.svg );
	background-repeat: no-repeat;
	background-position: 0 100%;
	font-size: 11px;
	line-height: 31px;
	padding: 0 5px;
	width: 100%;
}

.library-search {
	transition: width .5s ease-in-out;
	width: 200px;
}

.search-is-expanded .library-search {
	width: calc(100% - 28px);
}

.search-is-expanded .library-search-input {
	width: calc(100% - 28px);
}

.search-input-click-to-close {
	cursor: pointer;
	position: absolute;
	right: 0;
	top: 8px;
	z-index: 50;
}
</style>
