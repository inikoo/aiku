export const getStyles = (properties: any) => {
    if (properties) {
        return {
            paddingTop: (properties.padding.top.value || 0) + properties.padding.unit,
            paddingBottom: (properties.padding.bottom.value || 0) + properties.padding.unit,
            paddingRight: (properties.padding.right.value || 0) + properties.padding.unit,
            paddingLeft: (properties.padding.left.value || 0) + properties.padding.unit,
            marginTop: (properties.margin.top.value || 0) + properties.margin.unit,
            marginBottom: (properties.margin.bottom.value || 0) + properties.margin.unit,
            marginRight: (properties.margin.right.value || 0) + properties.margin.unit,
            marginLeft: (properties.margin.left.value || 0) + properties.margin.unit,
            background: properties?.background?.type === 'color' ? properties?.background?.color : properties?.background?.image,
            borderTop: `${properties.border.top.value}${properties.border.unit} solid ${properties.border.color}`,
            borderBottom: `${properties.border.bottom.value}${properties.border.unit} solid ${properties.border.color}`,
            borderRight: `${properties.border.right.value}${properties.border.unit} solid ${properties.border.color}`,
            borderLeft: `${properties.border.left.value}${properties.border.unit} solid ${properties.border.color}`,
            borderTopRightRadius: `${properties.border.rounded.topright.value}${properties.border.rounded.unit}`,
            borderBottomRightRadius: `${properties.border.rounded.bottomright.value}${properties.border.rounded.unit}`,
            borderBottomLeftRadius: `${properties.border.rounded.bottomleft.value}${properties.border.rounded.unit}`,
            borderTopLeftRadius: `${properties.border.rounded.topleft.value}${properties.border.rounded.unit}`,
        };
    } else {
        return
    }
}
