# For your laptop (x86_64)
GOOS=linux GOARCH=amd64 go build -o ./builds/stripey_horse_amd64

# For your server (ARM)
GOOS=linux GOARCH=arm64 go build -o ./builds/stripey_horse_arm64
