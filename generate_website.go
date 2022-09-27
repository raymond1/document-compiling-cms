package main

import (
	"fmt"
	"os"
)

func processScriptFile() {

}

func main() {
	if len(os.Args) == 1 {
		fmt.Printf("Usage: bash generate_website.sh <script filename>")
		os.Exit(0)
	}

	scriptFile := os.Args[1]
	content, err := os.ReadFile(scriptFile)
	if err != nil {
		fmt.Printf("Missing script.txt file.\n")
		os.Exit(0)
	}

	fmt.Printf(string(content))
}
