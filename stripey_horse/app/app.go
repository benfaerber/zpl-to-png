package app

import (
	"bytes"
	"fmt"
	"io"
	"os"

	"github.com/ingridhq/zebrash"
	"github.com/ingridhq/zebrash/drawers"
	"github.com/ingridhq/zebrash/elements"
)

func Run() error {
	config, outputPath, err := parseConfig()
	if err != nil {
		return err
	}

	zplData, err := readZPLFromStdin()
	if err != nil {
		return err
	}

	label, err := parseZPL(zplData)
	if err != nil {
		return err
	}

	imageData, err := generateLabelImage(label, config)
	if err != nil {
		return err
	}

	if config.Rotation != 0 {
		imageData, err = rotateImage(imageData, config.Rotation)
		if err != nil {
			return fmt.Errorf("rotating image: %w", err)
		}
	}

	return writeOutput(imageData, outputPath)
}

func readZPLFromStdin() ([]byte, error) {
	zplData, err := io.ReadAll(os.Stdin)
	if err != nil {
		return nil, fmt.Errorf("reading stdin: %w", err)
	}
	return zplData, nil
}

func parseZPL(zplData []byte) (elements.LabelInfo, error) {
	parser := zebrash.NewParser()
	labels, err := parser.Parse(zplData)
	if err != nil {
		return elements.LabelInfo{}, fmt.Errorf("parsing ZPL: %w", err)
	}

	if len(labels) == 0 {
		return elements.LabelInfo{}, fmt.Errorf("no labels found in ZPL content")
	}

	return labels[0], nil
}

func generateLabelImage(label elements.LabelInfo, config LabelConfig) ([]byte, error) {
	var buf bytes.Buffer
	drawer := zebrash.NewDrawer()
	opts := drawers.DrawerOptions{
		LabelWidthMm:  config.LabelWidthMm,
		LabelHeightMm: config.LabelHeightMm,
		Dpmm:          config.Dpmm,
	}

	if err := drawer.DrawLabelAsPng(label, &buf, opts); err != nil {
		return nil, fmt.Errorf("generating label image: %w", err)
	}

	return buf.Bytes(), nil
}

func writeOutput(data []byte, outputPath string) error {
	if outputPath != "" {
		if err := os.WriteFile(outputPath, data, 0644); err != nil {
			return fmt.Errorf("writing to file: %w", err)
		}
		return nil
	}

	if _, err := io.Copy(os.Stdout, bytes.NewReader(data)); err != nil {
		return fmt.Errorf("writing to stdout: %w", err)
	}
	return nil
}
