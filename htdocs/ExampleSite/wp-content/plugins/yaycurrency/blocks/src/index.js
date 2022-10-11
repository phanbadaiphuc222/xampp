import { __ } from "@wordpress/i18n";
import { registerBlockType } from "@wordpress/blocks";
import { InspectorControls } from "@wordpress/block-editor";
import {
  PanelBody,
  PanelRow,
  CheckboxControl,
  RadioControl,
} from "@wordpress/components";

registerBlockType("yay-currency/currency-switcher", {
  title: __("Currency Switcher - YayCurrency", "yay-currency"),
  icon: "index-card",
  category: "widgets",
  attributes: {
    currencyName: {
      type: "string",
      default: "United States dollar",
    },
    currencySymbol: {
      type: "string",
      default: "($)",
    },
    hyphen: {
      type: "string",
      default: " - ",
    },
    currencyCode: {
      type: "string",
      default: "USD",
    },
    isShowFlag: {
      type: "boolean",
      default: true,
    },
    isShowCurrencyName: {
      type: "boolean",
      default: true,
    },
    isShowCurrencySymbol: {
      type: "boolean",
      default: true,
    },
    isShowCurrencyCode: {
      type: "boolean",
      default: true,
    },
    widgetSize: {
      type: "string",
      default: "small",
    },
  },
  edit: (props) => {
    const {
      attributes: {
        currencyName,
        currencySymbol,
        hyphen,
        currencyCode,
        isShowFlag,
        isShowCurrencyName,
        isShowCurrencySymbol,
        isShowCurrencyCode,
        widgetSize,
      },
      setAttributes,
    } = props;

    const { yayCurrencyPluginURL } = yayCurrencyGutenberg;

    const renderSwitcherPreview = () => {
      isShowCurrencyName
        ? setAttributes({ currencyName: "United States dollar" })
        : setAttributes({ currencyName: "" });
      isShowCurrencySymbol
        ? isShowCurrencyName
          ? setAttributes({ currencySymbol: "($)" })
          : setAttributes({ currencySymbol: "$ " })
        : setAttributes({ currencySymbol: "" });
      isShowCurrencyCode
        ? setAttributes({ currencyCode: "USD" })
        : setAttributes({ currencyCode: "" });
      isShowCurrencyName && isShowCurrencyCode
        ? setAttributes({ hyphen: " - " })
        : setAttributes({ hyphen: "" });
      const result = `${currencyName} ${currencySymbol}${hyphen}${currencyCode}`;
      return result;
    };

    const renderFlag = () => {
      return (
        <span
          class={`yay-currency-flag selected ${widgetSize}`}
          style={{
            backgroundImage: `url(${yayCurrencyPluginURL}assets/dist/flags/us.svg)`,
            backgroundSize: "cover",
            backgroundRepeat: "no-repeat",
          }}
        ></span>
      );
    };

    const handleChangeIsShowFlag = (isChecked) => {
      setAttributes({ isShowFlag: isChecked });
    };
    const handleChangeIsShowCurrencyName = (isChecked) => {
      setAttributes({ isShowCurrencyName: isChecked });
    };
    const handleChangeIsShowCurrencySymbol = (isChecked) => {
      setAttributes({ isShowCurrencySymbol: isChecked });
    };
    const handleChangeIsShowCurrencyCode = (isChecked) => {
      setAttributes({ isShowCurrencyCode: isChecked });
    };

    const handleChangeWidgetSize = (size) => {
      setAttributes({ widgetSize: size });
    };

    const countDispalyElementsInWidget = () => {
      const elementKeys = [
        isShowFlag,
        isShowCurrencyName,
        isShowCurrencySymbol,
        isShowCurrencyCode,
      ];
      const displayElementsArray = [];
      elementKeys.forEach((element) => {
        element && displayElementsArray.push(element);
      });
      return displayElementsArray.length;
    };

    return [
      <InspectorControls>
        <PanelBody title={__("Switcher elements", "yay-currency")}>
          <CheckboxControl
            label="Show flag"
            checked={isShowFlag}
            onChange={handleChangeIsShowFlag}
          />
          <CheckboxControl
            label="Show currency name"
            checked={isShowCurrencyName}
            onChange={handleChangeIsShowCurrencyName}
          />
          <CheckboxControl
            label="Show currency symbol"
            checked={isShowCurrencySymbol}
            onChange={handleChangeIsShowCurrencySymbol}
          />
          <CheckboxControl
            label="Show currency code"
            checked={isShowCurrencyCode}
            onChange={handleChangeIsShowCurrencyCode}
          />
        </PanelBody>
        <PanelBody title={__("Switcher size", "yay-currency")}>
          <PanelRow>
            <RadioControl
              selected={widgetSize}
              options={[
                { label: "Small", value: "small" },
                { label: "Medium", value: "medium" },
              ]}
              onChange={handleChangeWidgetSize}
            />
          </PanelRow>
        </PanelBody>
      </InspectorControls>,
      <div
        className={`yay-currency-custom-select-wrapper ${widgetSize} ${!isShowCurrencyName &&
          "no-currency-name"} ${isShowCurrencyName &&
          !isShowFlag &&
          !isShowCurrencySymbol &&
          !isShowCurrencyCode &&
          "only-currency-name"}
          ${isShowCurrencyName &&
            countDispalyElementsInWidget() === 2 &&
            "only-currency-name-and-something"}
          `}
      >
        <div className="yay-currency-custom-select">
          <div className={`yay-currency-custom-select__trigger ${widgetSize}`}>
            <div className="yay-currency-custom-selected-option">
              {isShowFlag && renderFlag()}
              <span className="yay-currency-selected-option">
                {renderSwitcherPreview()}
              </span>
            </div>
            <div className="yay-currency-custom-arrow"></div>
          </div>
        </div>
      </div>,
    ];
  },
  save: () => null,
});