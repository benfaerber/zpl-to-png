#!/bin/bash

# Shell script to hit Labelary API for comparison with local ZPL renderer
# Based on LabelImage.php implementation

# Default values
ROTATION=180
LABEL_SIZE="4x6"
OUTPUT_FILE=""
ZPL_FILE=""

# Help function
show_help() {
    echo "Usage: $0 -f <zpl_file> [-s <label_size>] [-r <rotation>] [-o <output_file>]"
    echo ""
    echo "Options:"
    echo "  -f <zpl_file>     Path to ZPL file (required)"
    echo "  -s <label_size>   Label size: 4x6 (default) or 4x2"
    echo "  -r <rotation>     Rotation in degrees: 0, 90, 180, 270 (default: 0)"
    echo "  -o <output_file>  Output PNG file (optional, defaults to stdout)"
    echo "  -h                Show this help"
    echo ""
    echo "Examples:"
    echo "  $0 -f test_data/endicia_1.zplbin -o endicia_api.png"
    echo "  $0 -f test_data/ups_1.zplbin -s 4x2 -r 180"
}

# Parse command line arguments
while getopts "f:s:r:o:h" opt; do
    case $opt in
        f) ZPL_FILE="$OPTARG" ;;
        s) LABEL_SIZE="$OPTARG" ;;
        r) ROTATION="$OPTARG" ;;
        o) OUTPUT_FILE="$OPTARG" ;;
        h) show_help; exit 0 ;;
        *) show_help; exit 1 ;;
    esac
done

# Validate required arguments
if [ -z "$ZPL_FILE" ]; then
    echo "Error: ZPL file is required"
    show_help
    exit 1
fi

if [ ! -f "$ZPL_FILE" ]; then
    echo "Error: ZPL file '$ZPL_FILE' not found"
    exit 1
fi

# Validate label size and set URL
case $LABEL_SIZE in
    "4x6")
        API_URL="http://api.labelary.com/v1/printers/8dpmm/labels/4x6/0/"
        ;;
    "4x2")
        API_URL="http://api.labelary.com/v1/printers/8dpmm/labels/4x2/0/"
        ;;
    *)
        echo "Error: Invalid label size. Use 4x6 or 4x2"
        exit 1
        ;;
esac

# Validate rotation
case $ROTATION in
    0|90|180|270) ;;
    *)
        echo "Error: Invalid rotation. Use 0, 90, 180, or 270"
        exit 1
        ;;
esac

echo "Calling Labelary API..."
echo "URL: $API_URL"
echo "Label size: $LABEL_SIZE"
echo "Rotation: $ROTATION degrees"
echo "ZPL file: $ZPL_FILE"

# Make the API call
if [ -n "$OUTPUT_FILE" ]; then
    echo "Output: $OUTPUT_FILE"
    curl -X POST \
        -H "Accept: image/png" \
        -H "X-Rotation: $ROTATION" \
        -H "Content-Type: application/x-www-form-urlencoded" \
        --data-binary "@$ZPL_FILE" \
        "$API_URL" \
        -o "$OUTPUT_FILE" \
        -s

    if [ $? -eq 0 ]; then
        echo "✓ Generated: $OUTPUT_FILE"
    else
        echo "✗ Failed to generate image"
        exit 1
    fi
else
    echo "Output: stdout"
    curl -X POST \
        -H "Accept: image/png" \
        -H "X-Rotation: $ROTATION" \
        -H "Content-Type: application/x-www-form-urlencoded" \
        --data-binary "@$ZPL_FILE" \
        "$API_URL" \
        -s
fi
