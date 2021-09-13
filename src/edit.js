import { __ } from '@wordpress/i18n';
import { useBlockProps } from '@wordpress/block-editor';
import { SelectControl, ToggleControl } from '@wordpress/components';

import './editor.scss';


export default function Edit({ attributes, setAttributes }) {
	const { currency, showDatePicker } = attributes;

	const onCurrencyChange = (val) => {
		setAttributes({ currency: val });
	}

	const onShowDataPickerChange = (val) => {
		setAttributes({ showDatePicker: val });
	}

	return (
		<div {...useBlockProps()}>

			<SelectControl
				label={__('Wyświetlana waluta', 'exchangerate')}
				value={currency}
				style={{height: "60px"}}
				options={[
					{ label: __('Frank szwajcarski', 'exchangerate'), value: 'CHF' },
					{ label: __('Euro', 'exchangerate'), value: 'EUR' },
					{ label: __('Rupia indyjska', 'exchangerate'), value: 'INR' },
					{ label: __('Dolar amerykański', 'exchangerate'), value: 'USD' },
				]}
				onChange={(val) => onCurrencyChange(val)}
			/>

			<ToggleControl
				label={showDatePicker === true ? __('Wyświelaj wybór daty', 'exchangerate') : __('Nie wyświetlaj wyboru daty (data z poprzedniego dnia roboczego)', 'exchangerate')}
				checked={showDatePicker}
				onChange={() => onShowDataPickerChange(!showDatePicker)}
			/>

		</div>
	);
}
