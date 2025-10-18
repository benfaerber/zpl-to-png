#!/bin/bash

# Shell script to run the ZPL to PNG converter for all test data files

# Build the program first
echo "Building the program..."
./scripts/build.sh

# Check if build was successful
if [ $? -ne 0 ]; then
    echo "Build failed!"
    exit 1
fi

# Clean up old PNG files
echo "Cleaning up old PNG files..."
rm -f ./test_data/*.png
if [ $? -eq 0 ]; then
    echo "✓ Old PNG files removed"
fi

# Label configuration (4x6 inch label, common for shipping labels)
CONFIG='{"labelWidthMm": 101.6, "labelHeightMm": 152.4, "dpmm": 8, "rotation": 0}'
CONFIG_ROTATE='{"labelWidthMm": 101.6, "labelHeightMm": 152.4, "dpmm": 8, "rotation": 90}'

echo "Generating labels for all test data files..."

# Process all ZPL files in test_data directory
for zpl_file in ./test_data/*.zplbin; do
    if [ -f "$zpl_file" ]; then
        # Extract filename without extension
        basename=$(basename "$zpl_file" .zplbin)
        output_file="${basename}.png"

        echo "Processing $basename..."

        # Generate with local renderer
        ./builds/stripey_horse_amd64 --config "$CONFIG" --output "./test_data/$output_file" < "$zpl_file"
        ./builds/stripey_horse_amd64 --config "$CONFIG_ROTATE" --output "./test_data/rotate_$output_file" < "$zpl_file"
        ./builds/stripey_horse_amd64 --config "$CONFIG" < "$zpl_file"
        if [ $? -eq 0 ]; then
            echo "✓ Generated local: $output_file"
        else
            echo "✗ Failed to generate local: $output_file"
        fi

        # Generate with Labelary API for comparison
        api_output_file="${basename}_api.png"
        ./scripts/run_labelary_api.sh -f "$zpl_file" -o "./test_data/$api_output_file"
        if [ $? -eq 0 ]; then
            echo "✓ Generated API: $api_output_file"
        else
            echo "✗ Failed to generate API: $api_output_file"
        fi
    fi
done

echo "All labels processed."
