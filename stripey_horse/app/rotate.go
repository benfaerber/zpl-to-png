package app 

import (
	"bytes"
	"fmt"
	"image"
	"image/png"
)

func rotateImage(imageData []byte, degrees int) ([]byte, error) {
	img, err := png.Decode(bytes.NewReader(imageData))
	if err != nil {
		return nil, fmt.Errorf("decoding PNG: %w", err)
	}

	var rotated image.Image
	switch degrees {
	case 90:
		rotated = rotate90(img)
	case 270:
		rotated = rotate270(img)
  case 180:
    rotated = rotate180(img)
	default:
		return imageData, nil
	}

	var buf bytes.Buffer
	if err := png.Encode(&buf, rotated); err != nil {
		return nil, fmt.Errorf("encoding PNG: %w", err)
	}

	return buf.Bytes(), nil
}

func rotate90(img image.Image) image.Image {
	bounds := img.Bounds()
	w, h := bounds.Dx(), bounds.Dy()
	rotated := image.NewRGBA(image.Rect(0, 0, h, w))
	for y := range h {
		for x := range w {
			rotated.Set(y, w-1-x, img.At(x, y))
		}
	}
	return rotated
}

func rotate180(img image.Image) image.Image {
	bounds := img.Bounds()
	w, h := bounds.Dx(), bounds.Dy()
	rotated := image.NewRGBA(image.Rect(0, 0, w, h))
	for y := range h {
		for x := range w {
			rotated.Set(w-1-x, h-1-y, img.At(x, y))
		}
	}
	return rotated
}

func rotate270(img image.Image) image.Image {
	bounds := img.Bounds()
	w, h := bounds.Dx(), bounds.Dy()
	rotated := image.NewRGBA(image.Rect(0, 0, h, w))
	for y := range h {
		for x := range w {
			rotated.Set(h-1-y, x, img.At(x, y))
		}
	}
	return rotated
}
