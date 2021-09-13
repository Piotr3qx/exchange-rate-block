import { registerBlockType } from '@wordpress/blocks';
import './style.scss';
import Edit from './edit';

registerBlockType( 'create-block/exchange-rate-block', {
	edit: Edit,

	save: () => {
		return null
	},
} );
