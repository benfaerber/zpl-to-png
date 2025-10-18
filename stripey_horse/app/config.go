package app

import (
	"encoding/json"
	"fmt"
	"github.com/spf13/pflag"
)

type LabelConfig struct {
	LabelWidthMm  float64 `json:"labelWidthMm"`
	LabelHeightMm float64 `json:"labelHeightMm"`
	Dpmm          int     `json:"dpmm"`
	Rotation      int     `json:"rotation"`
}

func printHelpMenu() {
  fmt.Println("stripey_horse: a knock-off zebra renderer")
  fmt.Println("https://github.com/trueleafmarket-dg/stripey_horse")
  fmt.Println("")
  
  pflag.Usage()
} 

func parseConfig() (LabelConfig, string, error) {
	configJSON := pflag.StringP("config", "c", "", "JSON configuration for label dimensions")
	outputFile := pflag.StringP("output", "o", "", "Output file path (defaults to stdout)")
	pflag.Parse()

	if *configJSON == "" {
    printHelpMenu()
		return LabelConfig{}, "", fmt.Errorf("config is required")
	}

	var config LabelConfig
	if err := json.Unmarshal([]byte(*configJSON), &config); err != nil {
		return LabelConfig{}, "", fmt.Errorf("parsing config JSON: %w", err)
	}

	return config, *outputFile, nil
}
