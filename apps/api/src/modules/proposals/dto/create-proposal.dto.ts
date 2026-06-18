import { IsString, IsNotEmpty, IsNumber, IsPositive, IsOptional } from "class-validator";

export class CreateProposalDto {
  @IsString()
  @IsNotEmpty()
  coverLetter: string;

  @IsNumber()
  @IsPositive()
  amount: number;

  @IsOptional()
  @IsString()
  timeline?: string;
}
